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
				'workflow_version',
				'z',
				InputOption::VALUE_REQUIRED,
				'[Required] The version of the workflow type that we should register. See config value at uecode.amazon.simpleworkflow.domain.[domain].workflows.[workflow_version]'
			)
			->addOption(
				'tasklist',
				't',
				InputOption::VALUE_REQUIRED,
				'[Required] The SWF taskList to poll on. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html#SWF-PollForDecisionTask-request-taskList.'
			)
			->addOption(
				'default_child_policy',
				'c',
				InputOption::VALUE_REQUIRED,
				'The SWF workflow defaultChildPolicy sent during registration. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultChildPolicy.'
			)
			->addOption(
				'default_task_list',
				'l',
				InputOption::VALUE_REQUIRED,
				'The SWF workflow defaultTaskList sent during registration. See  http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultTaskList'
			)
			->addOption(
				'default_task_timeout',
				'o',
				InputOption::VALUE_REQUIRED,
				'The SWF workflow defaultTaskStartToCloseTimeout sent during registration. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultTaskStartToCloseTimeout.'
			)
			->addOption(
				'default_execution_timeout',
				'p',
				InputOption::VALUE_REQUIRED,
				'The SWF workflow defaultExecutionStartToCloseTimeout sent during registration. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultExecutionStartToCloseTimeout.'
			)
			->addOption(
				'event_namespace',
				'b',
				InputOption::VALUE_REQUIRED,
				'Where your event classes are located'
			)
			->addOption(
				'activity_event_namespace',
				'a',
				InputOption::VALUE_REQUIRED,
				'Where your activity classes are located'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

		// default values
		$version = null;
		$taskList = null;
		$defaultChildPolicy = null;
		$defaultTaskList = null;
		$defaultTaskStartToCloseTimeout = null;
		$defaultExecutionStartToCloseTimeout = null;
		$eventNamespace = null;
		$activityNamespace = null;

		$domain = $input->getOption('domain');
		$name = $input->getOption('workflow_name');
		$taskList = $input->getOption('tasklist');
		$version = $input->getOption('workflow_version');

		if (empty($domain) || empty($name) || empty($taskList) || empty($version)) {
			throw new \Exception('--domain, --workflow_name, --workflow_version, and --tasklist are requried options.');
		}

		$logger = $container->get('logger');

		try {
			$amazonFactory = $container->get('uecode.amazon')->getFactory('ue');

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
					'default_task_timeout' => $defaultTaskStartToCloseTimeout,
					'default_execution_timeout' => $defaultExecutionStartToCloseTimeout,
					'eventNamespace' => $eventNamespace,
					'activityNamespace' => $activityNamespace
				)
			);

			$swf = $amazonFactory->build('AmazonSWF', array('domain' => $domain), $container);
			$decider = $swf->loadDecider($domain, $name, $version, $taskList);

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
