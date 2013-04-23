<?php

/**
 * Start an activity worker.
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
			$swf->addDb('database_connection', $container->get('database_connection'));
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
