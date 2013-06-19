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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

class DeciderWorkerCommand extends ContainerAwareCommand
{
	protected function configure() {
		$this
			->setName('ue:aws:swf:decider_worker')
			->setDescription('Start a decider worker which will register the worker then poll amazon for a decision task. The "domain", "name", and "task_list" arguments are required and they both specify config params at uecode.amazon.simpleworkflow.domains.[<domain>].workflows.[<name>]. The rest of the config values can be overridden w/ their respective options to this command.')
			->addArgument(
				'domain',
				InputArgument::REQUIRED,
				'The SWF workflow domain. Used for registration and polling. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-domain and http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html#SWF-PollForDecisionTask-request-domain.'
			)
			->addArgument(
				'name',
				InputArgument::REQUIRED,
				'The SWF workflow name. Used for registration. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-name.'
			)
			->addArgument(
				'task_list',
				null,
				InputArgument::REQUIRED,
				'The SWF taskList to poll on. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html#SWF-PollForDecisionTask-request-taskList.'
			)
			->addOption(
				'workflow_version',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow version.'
			)
			->addOption(
				'default_child_policy',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow defaultChildPolicy sent during registration. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultChildPolicy.'
			)
			->addOption(
				'default_task_list',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow defaultTaskList sent during registration. See  http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultTaskList'
			)
			->addOption(
				'default_task_start_to_close_timeout',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow defaultTaskStartToCloseTimeout sent during registration. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultTaskStartToCloseTimeout.'
			)
			->addOption(
				'default_execution_start_to_close_timeout',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow defaultExecutionStartToCloseTimeout sent during registration. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultExecutionStartToCloseTimeout.'
			)
			->addOption(
				'event_namespace',
				null,
				InputOption::VALUE_REQUIRED,
				'Where your event classes are located'
			)
			->addOption(
				'activity_event_namespace',
				null,
				InputOption::VALUE_REQUIRED,
				'Where your activity classes are located'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

		$logger = $container->get('logger');

		try {
			// default values
			$version = null;
			$taskList = null;
			$defaultChildPolicy = null;
			$defaultTaskList = null;
			$defaultTaskStartToCloseTimeout = null;
			$defaultExecutionStartToCloseTimeout = null;
			$eventNamespace = null;
			$activityNamespace = null;

			$logger->log(
				'debug',
				'About to start decider worker'
			);

			$amazonFactory = $container->get('uecode.amazon')->getFactory('ue');

			$domain = $input->getArgument('domain');
			$name = $input->getArgument('name');
			$taskList = $input->getArgument('task_list');

			$cfg = $amazonFactory->getConfig()->get('simpleworkflow');

			foreach ($cfg['domains'] as $dk => $dv) {
				if ($dk == $domain) {
					foreach ($dv['workflows'] as $kk => $kv) {
						if ($kk == $name) {
							$version = $kv['version'];
							$defaultChildPolicy = $kv['default_child_policy'];
							$defaultTaskList = $kv['default_task_list'];
							$defaultTaskStartToCloseTimeout = isset($kv['default_task_start_to_close_timeout']) ? $kv['default_task_start_to_close_timeout'] : null;
							$defaultExecutionStartToCloseTimeout = isset($kv['default_execution_start_to_close_timeout']) ? $kv['default_execution_start_to_close_timeout'] : null;
							$eventNamespace = $kv['history_event_namespace'];
							$activityNamespace = $kv['history_activity_event_namespace'];
						}
					}
				}
			}

			// allow config to be overridden by passed values.
			$version = $input->getOption('workflow_version') ?: $version;
			$defaultChildPolicy = $input->getOption('default_child_policy') ?: $defaultChildPolicy;
			$defaultTaskList = $input->getOption('default_task_list') ?: $defaultTaskList;
			$defaultTaskStartToCloseTimeout = $input->getOption('default_task_start_to_close_timeout') ?: $defaultTaskStartToCloseTimeout;
			$defaultExecutionStartToCloseTimeout = $input->getOption('default_execution_start_to_close_timeout') ?: $defaultExecutionStartToCloseTimeout;
			$eventNamespace = $input->getOption('event_namespace') ?: $eventNamespace;
			$activityNamespace = $input->getOption('activity_event_namespace') ?: $activityNamespace;

			if (empty($domain)
			 || empty($name)
			 || empty($version)
			 || empty($taskList)
			 || empty($eventNamespace)
			 || empty($activityNamespace)) {
				throw new \Exception('Decider/workflow is misconfigured.');
			}

			$logger->log(
				'info',
				'Starting decider worker',
				array(
					'domain' => $domain,
					'name' => $name,
					'version' => $version,
					'task_list' => $taskList,
					'default_child_policy' => $defaultChildPolicy,
					'default_task_list' => $defaultTaskList,
					'default_task_start_to_close_timeout' => $defaultTaskStartToCloseTimeout,
					'default_execution_start_to_close_timeout' => $defaultExecutionStartToCloseTimeout,
					'eventNamespace' => $eventNamespace,
					'activityNamespace' => $activityNamespace
				)
			);

			$swf = $amazonFactory->build('AmazonSWF', array('domain' => $domain), $container);
			$decider = $swf->loadDecider($domain, $name, $version, $taskList, $defaultChildPolicy, $defaultTaskList, $defaultTaskStartToCloseTimeout, $defaultExecutionStartToCloseTimeout, $eventNamespace, $activityNamespace);

			// note that run() will sit in an infinite loop unless this process is killed.
			// it's better to use SIGHUP, SIGINT, or SIGTERM than SIGKILL since the workers
			// have signal handlers.
			$decider->run();

			$output->writeln('exiting');
			$decider->log(
				'info',
				'Decider worker ended'
			);
		} catch (\Exception $e) {
			try {
				$logger->log(
					'critical',
					'Caught exception: '.$e->getMessage(),
					array(
						'trace' => $e->getTrace()
					)
				);
			// if that failed... then... damn...
			} catch (Exception $e) {
				$output->writeln('EXCEPTION: '.$e->getMessage());
				$output->writeln(print_r($e, true));
			}
		}
	}
}
