<?php

/**
 * Start a decider worker.
 *
 * @author John Pancoast
 * @date 2013-03-20
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
				'history_event_namespace',
				null,
				InputOption::VALUE_REQUIRED,
				'Where your event classes are located'
			)
			->addOption(
				'history_activity_event_namespace',
				null,
				InputOption::VALUE_REQUIRED,
				'Where your activity classes are located'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

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
						$eventNamespace = $kv['event_namespace'];
						$activityNamespace = $kv['activity_namespace'];
					}
				}
			}
		} else {
			$domain = $input->getOption('domain');
			$name = $input->getOption('name');
			$version = $input->getOption('workflow_version');
			$taskList = $input->getOption('taskList');
			$eventNamespace = $input->getOption('history_event_namespace');
			$activityNamespace = $input->getOption('history_activity_event_namespace');
		}

		$swf = $amazonFactory->build('AmazonSWF', array('domain' => $domain));
		$decider = $swf->loadDecider($name, $version, $taskList, $eventNamespace, $activityNamespace);
		$decider->run();

		$output->writeln('done');
	}
}
