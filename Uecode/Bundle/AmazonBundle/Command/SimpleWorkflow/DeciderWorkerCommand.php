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
			->setDescription('Start a decider worker which will poll amazon for a decision task. You can either pass [domain, name, taskList] for a custom call or you can pass the "config id" which would correlate to a config value at uecode.amazon.simpleworkflow.domains.[name].workflows.<config id>')
			->addOption(
				'config_key',
				null,
				InputOption::VALUE_REQUIRED,
				'The config id which correlates to config value at uecode.amazon.simpleworkflow.domains.[name].workflows.<config id>'
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
				'activity_namespace',
				null,
				InputOption::VALUE_REQUIRED,
				'Where your activity classes are located'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

		$amazonFactory = $container->get( 'uecode.amazon' )->getFactory( 'ue' );

		$configKey = $input->getOption('config_key');

		// FIXME - This _should_ work by just passing the config key to SimpleWorkflow::loadDeciderFromConfig()
		// but due to the current class architeture's requirements, we 
		// have to pull out the domain to pass as option to AmazonFactory::build().
		// Fix the class architecture. The core of the problem is that
		// SimpleWorkflow extends SWF when it should encapsulate it.
		if ($configKey) {
			$cfg = $amazonFactory->getConfig()->get('simpleworkflow');

			foreach ($cfg['domains'] as $dk => $dv) {
				foreach ($dv['workflows'] as $kk => $kv) {
					if ($kk == $configKey) {
						$domain = $dk;
					}
				}
			}

			$swf = $amazonFactory->build( 'AmazonSWF', array( 'domain' => $domain ) );
			$worker = $swf->loadDeciderFromConfig($configKey);
		} else {
			$domain = $input->getOption('domain');
			$name = $input->getOption('name');
			$version = $input->getOption('workflow_version');
			$taskList = $input->getOption('taskList');
			$eventNamespace = $input->getOption('event_namespace');
			$activityNamespace = $input->getOption('activity_namespace');

			$swf = $amazonFactory->build( 'AmazonSWF', array( 'domain' => $domain ) );
			$worker = $swf->loadDecider( $name, $version, $taskList, $eventNamespace, $activityNamespace );
		}

		$worker->run();
		$output->writeln('done');
	}
}
