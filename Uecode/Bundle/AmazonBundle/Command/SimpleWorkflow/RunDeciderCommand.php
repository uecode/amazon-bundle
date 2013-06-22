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
				'y',
				InputOption::VALUE_REQUIRED,
				'[Required] The version of the workflow type that we should register. See config value at uecode.amazon.simpleworkflow.domain.[domain].workflows.[workflow_version]'
			)
			->addOption(
				'activity_version',
				'z',
				InputOption::VALUE_REQUIRED,
				'[Required] The version of the activities that wwill be registered. See config value at uecode.amazon.simpleworkflow.domain.[domain].workflows.[].version'
			)
			->addOption(
				'tasklist',
				't',
				InputOption::VALUE_REQUIRED,
				'[Required] The SWF taskList to poll on. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html#SWF-PollForDecisionTask-request-taskList.'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

		$domain = $input->getOption('domain');
		$name = $input->getOption('workflow_name');
		$taskList = $input->getOption('tasklist');
		$workflowVersion = $input->getOption('workflow_version');
		$activityVersion = $input->getOption('activity_version');

		if (empty($domain) || empty($name) || empty($taskList) || empty($workflowVersion) || empty($activityVersion)) {
			throw new \Exception('--domain, --workflow_name, --workflow_version, activity_version, and --tasklist are requried options.');
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
					'workflow_version' => $workflowVersion,
					'activity_version' => $activityVersion,
					'task_list' => $taskList,
				)
			);

			$swf = $amazonFactory->build('AmazonSWF', array(), $container);
			$decider = $swf->loadDecider($domain, $name, $workflowVersion, $activityVersion, $taskList);

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
