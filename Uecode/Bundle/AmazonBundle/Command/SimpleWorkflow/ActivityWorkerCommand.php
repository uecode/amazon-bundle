<?php
/**
 * Start an activity worker.
 *
 * @author John Pancoast
 * @date 2013-03-28
 */

namespace Uecode\Bundle\AmazonBundle\Command\SimpleWorkflow;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

class ActivityWorkerCommand extends ContainerAwareCommand
{
	protected function configure() {
		$this
			->setName('ue:aws:simpleworkflow:activityworker')
			->setDescription('Start an activity worker which will poll amazon for an activity task. You can either pass [domain, tasklist, namespace] for a custom call or you can pass a "config_key" which correlates to the config values at uecode.amazon.simpleworkflow.domains.[name].activity_tasklists.[<config_key>]. \'identity\' is always passed on it\'s own.')
			->addOption(
				'config_key',
				null,
				InputOption::VALUE_REQUIRED,
				'The config id which correlates to config value at uecode.amazon.simpleworkflow.domains.[name].activity_tasklists.[<config_key>]'
			)
			->addOption(
				'domain',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow domain'
			)
			->addOption(
				'tasklist',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF activity tasklist'
			)
			->addOption(
				'identity',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF activity identity'
			)
			->addOption(
				'namespace',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF activity namespace. Where activity classes are located.'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

		$amazonFactory = $container->get( 'uecode.amazon' )->getFactory( 'ue' );

		$configKey = $input->getOption('config_key');

		if ($configKey) {
			$cfg = $amazonFactory->getConfig()->get('simpleworkflow');

			foreach ($cfg['domains'] as $dk => $dv) {
				foreach ($dv['activity_tasklists'] as $tk => $tv) {
					if ($tk == $configKey) {
						$domain = $dk;
						$taskList = $tv['name'];
						$namespace = $tv['namespace'];
					}
				}
			}
		} else {
			$domain = $input->getOption('domain');
			$taskList = $input->getOption('tasklist');
			$namespace = $input->getOption('namespace');
		}

		// identity always set at runtime (not from config). this
		// is cus 2 of the same activity task list can have diff identities.
		$identity = $input->getOption('identity');

		$swf = $amazonFactory->build('AmazonSWF', array('domain' => $domain));
		$activity = $swf->loadActivity($taskList, $namespace, $identity);
		$activity->run();

		$output->writeln('done');
	}
}
