<?php

/**
 * Start a decider worker.
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author John Pancoast
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
			->setName('ue:aws:simpleworkflow:deciderworker')
			->setDescription('Start a decider worker which will poll amazon for a decision task. You can either pass [domain, name, taskList] for a custom call or you can pass a "config_key" which correlates to config values at uecode.amazon.simpleworkflow.domains.[name].workflows.[<config_key>]')
			->addOption(
				'config_key',
				null,
				InputOption::VALUE_REQUIRED,
				'The config key which correlates to config values at uecode.amazon.simpleworkflow.domains.[name].workflows.[<config_key>]'
			)
			->addOption(
				'domain',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow domain'
			)
			->addOption(
				'name',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow name.'
			)
			->addOption(
				'workflow_version',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow name.'
			)
			->addOption(
				'taskList',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow taskList'
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
			$logger->log(
				'info',
				'About to start decider worker'
			);

			$amazonFactory = $container->get( 'uecode.amazon' )->getFactory( 'ue' );

			$configKey = $input->getOption('config_key');

			if ($configKey) {
				$cfg = $amazonFactory->getConfig()->get('simpleworkflow');

				foreach ($cfg['domains'] as $dk => $dv) {
					foreach ($dv['workflows'] as $kk => $kv) {
						if ($kk == $configKey) {
							$domain = $dk;
							$name = $kv['name'];
							$version = $kv['version'];
							$taskList = $kv['default_task_list'];
							$eventNamespace = $kv['history_event_namespace'];
							$activityNamespace = $kv['history_activity_event_namespace'];
						}
					}
				}
			} else {
				$domain = $input->getOption('domain');
				$name = $input->getOption('name');
				$version = $input->getOption('workflow_version');
				$taskList = $input->getOption('taskList');
				$eventNamespace = $input->getOption('event_namespace');
				$activityNamespace = $input->getOption('activity_event_namespace');
			}

			$logger->log(
				'info',
				'Starting decider worker',
				array(
					'domain' => $domain,
					'name' => $name,
					'version' => $version,
					'taskList' => $taskList,
					'eventNamespace' => $eventNamespace,
					'activityNamespace' => $activityNamespace
				)
			);

			$swf = $amazonFactory->build('AmazonSWF', array('domain' => $domain));
			$decider = $swf->loadDecider($name, $version, $taskList, $eventNamespace, $activityNamespace);

			// note that run() will sit in a loop while(true).
			$decider->run();

			$output->writeln('done');
			$logger->log(
				'info',
				'Decider worker ended'
			);
		} catch (\Exception $e) {
			// if this fails... then... damn...
			try {
				$logger->log(
					'error',
					'Caught exception: '.$e->getMessage(),
					$e->getTrace()
				);
			} catch (Exception $e) {
				echo 'EXCEPTION: '.$e->getMessage()."\n";
			}
		}
	}
}
