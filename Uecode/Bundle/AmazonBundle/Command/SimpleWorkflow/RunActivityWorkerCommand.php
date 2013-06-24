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

class RunActivityWorkerCommand extends ContainerAwareCommand
{
	protected function configure() {
		$this
			->setName('ue:aws:swf:run_activity_worker')
			->setDescription('Start an activity worker which will poll amazon for an activity task.')
			->addOption(
				'domain',
				'd',
				InputOption::VALUE_REQUIRED,
				'[Required] The SWF workflow domain config key. Used both for registration and polling for activities in this domain.'
			)
			->addOption(
				'tasklist',
				't',
				InputOption::VALUE_REQUIRED,
				'[Required] The SWF activity tasklist. Used for polling.'
			)
			->addOption(
				'identity',
				'i',
				InputOption::VALUE_REQUIRED,
				'The SWF activity identity. Used for polling.'
			)
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

		$logger = $container->get('logger');

		try {
			$domain = $input->getOption('domain');
			$taskList = $input->getOption('tasklist');
			$identity = $input->getOption('identity');

			if (!$domain || !$taskList) {
				throw new \Exception('Must define --domain, --tasklist, and --activity_version');
			}

			$logger->log(
				'info',
				'Starting activity worker',
				array(
					'domain' => $domain,
					'taskList' => $taskList,
					'identity' => $identity,
				)
			);

			// this will sit in an infinite loop (only while code conditions stay true).
			// it is best to send this a SIGHUP, SIGINT, or SIGTERM so it ends nicely.
			$container->get('uecode.amazon')
			          ->getAmazonService('SimpleWorkflow', 'ue', array('domain' => $domain))
			          ->runActivityWorker($taskList, $identity);

			$output->writeln('exiting');

			$logger->log(
				'info',
				'Activity worker ended'
			);
		} catch (\Exception $e) {
			$logger->log(
				'critical',
				'Caught exception: '.$e->getMessage(),
				array(
					'trace' => $e->getTrace()
				)
			);

			throw $e;
		}
	}
}
