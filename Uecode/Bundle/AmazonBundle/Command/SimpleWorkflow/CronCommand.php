<?php

/**
 * Start a decider worker.
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author John Pancoast
 *
 * Copyright 2013 Underground Elephant
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Uecode\Bundle\AmazonBundle\Command\SimpleWorkflow;

//use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Process\Process;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

/**
 * Start and stop decider and activity workers based on counts that are
 * specified in your config. You specify the counts for your deciders and
 * activity task lists. The values are respectively
 * "uecode.amazon.simpleworkflow.domains.[domain].cron.deciders.[name].count" and
 * "uecode.amazon.simpleworkflow.domains.[domain].cron.activities.[task_list].count".
 *
 * It's suggested that you run this script from cron every 2 minutes.
 *
 * NOTE THAT THIS COMMAND IS TEMPORARY AND WE HAVE FUTURE PLANS FOR PROC MGMT.
 * ADDITIONALLY, THERE EXISTS A RACE CONDITION IF YOU RUN THIS SCRIPT OFTEN,
 * HOWEVER, IT IS RELATIVELY HARMLESS AND IRRELEVANT IF YOU LET CRON RUN THIS
 * SCRIPT EVERY TWO MINUTES.
 */
class CronCommand extends ContainerAwareCommand
{
	protected function configure() {
		$this
			->setName('ue:aws:simpleworkflow:cron')
			->setDescription('Start and stop decider and activity workers based on counts that are specified in your config. You specify the counts for your deciders and activity task lists. The values are respectively "uecode.amazon.simpleworkflow.domains.[domain].cron.deciders.[name].count" and "uecode.amazon.simpleworkflow.domains.[domain].cron.activities.[task_list].count". It\'s suggested that you run this script from cron every 2 minutes. NOTE THAT THIS COMMAND IS TEMPORARY AND WE HAVE FUTURE PLANS FOR PROC MGMT. ADDITIONALLY, THERE EXISTS A RACE CONDITION IF YOU RUN THIS SCRIPT OFTEN, HOWEVER, IT IS RELATIVELY HARMLESS AND IRRELEVANT IF YOU LET CRON RUN THIS SCRIPT.')
			->addOption(
				'update',
				'u',
				InputOption::VALUE_NONE,
				'Reload values from config and start/stop workers based on the config counts.'
			)
			->addOption(
				'kill_hard',
				'k',
				InputOption::VALUE_NONE,
				'All kill operations will send a -9 (SIGHUP) signal'
			)
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		// for now all we care about is the --update option but eventually
		// maybe user can set counts manually (hence the requirement for the update option).
		$update = $input->getOption('update');
		if (!$update) {
			$output->writeln('Did nothing...');
			return;
		}

		$kill9 = $input->getOption('kill_hard');

		$kernel = $this->getApplication()->getKernel();
		$container = $kernel->getContainer();

		$rootDir = $kernel->getRootDir();
		$logger = $container->get('logger');

		$cfg = $container->get('uecode.amazon')
		  ->getFactory('ue')
		  ->getConfig()
		  ->get('simpleworkflow');

		foreach ($cfg['domains'] as $domainName => $domain) {
			foreach ($domain['workflows'] as $workflowName => $workflow) {
				// must have count for any of this to be relevant
				if (!isset($workflow['run_counts'])) {
					$output->writeln('  skipped...');
					continue;
				}

				foreach ($workflow['run_counts'] as $taskListName => $arr) {
					$targetCount = $arr['count'];
					if (!is_numeric($targetCount)) {
						$output->writeln('  skipped...');
						continue;
					}

					$output->writeln('Handling decider worker counts for '.$domainName.'.'.$workflowName.'.'.$taskListName);

					$procStr = "console ue:aws:simpleworkflow:deciderworker $domainName $workflowName $taskListName -e ".$kernel->getEnvironment();

					$pids = array();
					$process = new Process('ps -ef | grep "'.$procStr.'" | grep -v grep | awk \'{print $2}\'');
					$process->setTimeout(5);
					$process->run();

					if (!$process->isSuccessful()) {
						throw new \Exception($process->getErrorOutput());
					}

					foreach (explode("\n", $process->getOutput()) as $line) {
						if (is_numeric($line)) {
							$pids[] = $line;
						}
					}

					$currentCount = count($pids);

					$output->writeln('  Current process count: '.$currentCount.', target count: '.$targetCount);

					// kill processes
					if ($currentCount > $targetCount) {
						$output->writeln('  Killing '.($currentCount-$targetCount).' decider workers');
						$killed = array();
						for ($i = 0, $currentCount; $currentCount > $targetCount; --$currentCount, ++$i) {
							$pid = $pids[$i];
							$process = new Process("kill ".($kill9 ? '-9' : '')." $pid");
							$process->setTimeout(5);
							$process->run();
							if (!$process->isSuccessful()) {
								throw new \Exception($process->getErrorOutput());
							}

							$killed[] = $pid;
						}

						if ($kill9) {
							$output->writeln("  Sent a SIGKILL signal to the following PIDs:\n  ".implode(', ', $killed));
						} else {
							$output->writeln("  Sent a SIGTERM signal to the following PIDs. They will each finish their current job before exiting:\n  ".implode(', ', $killed));
						}

					// start processes
					} elseif ($currentCount < $targetCount) {
						$output->writeln('  Starting '.($targetCount-$currentCount).' decider workers.');

						for (; $currentCount < $targetCount; ++$currentCount) {
							// use exec() becuase from what I can tell, Process class can't
							// do a background job.
							exec(escapeshellcmd("$rootDir/$procStr").' > /dev/null &');
							usleep(1000);
						}
					}
				}
			}

			// need counts for any of this to be relevant
			if (!isset($domain['activities']['run_counts'])) {
				continue;
			}

			$output->writeln('Handling activity worker counts for '.$domainName.'.'.$taskListName);

			foreach ($domain['activities']['run_counts'] as $taskListName => $arr) {
				$targetCount = $arr['count'];
				if (!is_numeric($targetCount)) {
					$output->writeln('  skipped...');
					continue;
				}

				$procStr = "console ue:aws:simpleworkflow:activityworker $domainName $taskListName -e ".$kernel->getEnvironment();

				$pids = array();
				$process = new Process('ps -ef | grep "'.$procStr.'" | grep -v grep | awk \'{print $2}\'');
				$process->setTimeout(5);
				$process->run();
				if (!$process->isSuccessful()) {
					throw new \Exception($process->getErrorOutput());
				}

				foreach (explode("\n", $process->getOutput()) as $line) {
					if (is_numeric($line)) {
						$pids[] = $line;
					}
				}

				$currentCount = count($pids);

				$output->writeln('  Current process count: '.$currentCount.', target count: '.$targetCount);

				// kill processes
				if ($currentCount > $targetCount) {
					$output->writeln('  Killing '.($currentCount-$targetCount).' activity workers');

					$killed = array();
					for ($i = 0, $currentCount; $currentCount > $targetCount; --$currentCount, ++$i) {
						$pid = $pids[$i];
						$process = new Process("kill ".($kill9 ? '-9' : '')." $pid");
						$process->setTimeout(5);
						$process->run();
						if (!$process->isSuccessful()) {
							throw new \Exception($process->getErrorOutput());
						}

						$killed[] = $pid;
					}

					if ($kill9) {
						$output->writeln("  Sent a SIGKILL signal to the following PIDs:\n  ".implode(', ', $killed));
					} else {
						$output->writeln("  Sent a SIGTERM signal to the following PIDs. They will each finish their current job before exiting:\n  ".implode(', ', $killed));
					}
				// start processes
				} elseif ($currentCount < $targetCount) {
					$output->writeln('  Starting '.($targetCount-$currentCount).' activity workers.');
					for (; $currentCount < $targetCount; ++$currentCount) {
						// use exec() becuase from what I can tell, Process class can't
						// do a background job.
						exec(escapeshellcmd("$rootDir/$procStr").' > /dev/null &');
						usleep(1000);
					}
				}
			}
		}
	}
}
