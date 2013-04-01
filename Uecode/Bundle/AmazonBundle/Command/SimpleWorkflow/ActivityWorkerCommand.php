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
			->setDescription('Start an activity worker which will poll amazon for an activity task.')
			->addOption(
				'domain',
				null,
				InputOption::VALUE_REQUIRED,
				'The SWF workflow domain config key.'
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
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

		$amazonFactory = $container->get( 'uecode.amazon' )->getFactory( 'ue' );

		$domain = $input->getOption('domain');
		$taskList = $input->getOption('tasklist');
		$identity = $input->getOption('identity');
		$namespace = null;

		$cfg = $amazonFactory->getConfig()->get('simpleworkflow');
		foreach ($cfg['domains'] as $dk => $dv) {
			if ($dk == $domain) {
				$namespace = $dv['activity_namespace'];
			}
		}

		$swf = $amazonFactory->build('AmazonSWF', array('domain' => $domain));
		$activity = $swf->loadActivity($taskList, $namespace, $identity);
		$activity->run();

		$output->writeln('done');
	}
}
