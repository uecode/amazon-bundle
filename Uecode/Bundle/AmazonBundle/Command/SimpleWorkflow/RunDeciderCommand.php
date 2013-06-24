<?php

/**
 * Start a decider.
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

class RunDeciderCommand extends ContainerAwareCommand
{
	protected function configure() {
		$this
			->setName('ue:aws:swf:run_decider')
			->setDescription('Start a decider worker which will register the worker then poll amazon for a decision task. The "domain", "name", and "tasklist" arguments are required. "domain" and "name" specify config params at uecode.amazon.simpleworkflow.domains.[<domain>].workflows.[<name>]. The rest of the config values can be overridden w/ their respective options to this command.')
			->addOption(
				'domain',
				'd',
				InputOption::VALUE_REQUIRED,
				'[Required] The SWF workflow domain. Used for registration and polling. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-domain and http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html#SWF-PollForDecisionTask-request-domain.'
			)
			->addOption(
				'workflow_name',
				'w',
				InputOption::VALUE_REQUIRED,
				'[Required] The SWF workflow name. Used for registration. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-name.'
			)
			->addOption(
				'tasklist',
				't',
				InputOption::VALUE_REQUIRED,
				'[Required] The SWF taskList to poll on. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html#SWF-PollForDecisionTask-request-taskList.'
			)
			->addOption(
				'register',
				'r',
				null,
				'Register all workflows and activities you have specified in your config before we make a poll request.'
			)
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

		$domain = $input->getOption('domain');
		$name = $input->getOption('workflow_name');
		$taskList = $input->getOption('tasklist');
		$register = $input->getOption('register');

		if (empty($domain) || empty($name) || empty($taskList)) {
			throw new \Exception('--domain, --workflow_name, and --tasklist are requried options.');
		}

		$logger = $container->get('logger');

		try {
			$logger->log(
				'info',
				'Starting decider worker',
				array(
					'domain' => $domain,
					'name' => $name,
					'task_list' => $taskList,
				)
			);

			$swf = $container->get('uecode.amazon')->getAmazonService('SimpleWorkflow', 'ue');

			if ($register) {
				$this->registerDomain($domain);
				$this->registerWorkflow($domain);
				$this->registerActivities($domain);
			}

			// this will sit in an infinite loop (only while code conditions stay true).
			// it is best to send this a SIGHUP, SIGINT, or SIGTERM so it ends nicely.
			$swf->runDecider($domain, $name, $taskList);

			$output->writeln('exiting');

			$logger->log(
				'info',
				'Decider worker ended'
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
