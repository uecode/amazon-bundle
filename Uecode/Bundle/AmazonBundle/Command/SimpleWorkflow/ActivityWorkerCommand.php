<?php

/**
 * Start an activity worker.
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author John Pancoast
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

		$logger = $container->get('logger');

		try {
			$logger->log(
				'info',
				'About to start activity worker'
			);

			$amazonFactory = $container->get( 'uecode.amazon' )->getFactory( 'ue' );

			$domain = $input->getOption('domain');
			$taskList = $input->getOption('tasklist');
			$identity = $input->getOption('identity');

			$logger->log(
				'info',
				'Starting activity worker',
				array(
					'domain' => $domain,
					'taskList' => $taskList,
					'identity' => $identity,
				)
			);

			$swf = $amazonFactory->build('AmazonSWF', array('domain' => $domain));
			$activity = $swf->loadActivity($taskList, $identity);

			// note that run() will sit in a loop while(true).
			$activity->run();

			$output->writeln('done');

			$logger->log(
				'info',
				'Activity worker ended'
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
