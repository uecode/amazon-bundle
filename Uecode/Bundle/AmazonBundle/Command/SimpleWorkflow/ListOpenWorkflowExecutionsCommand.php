<?php
/**
 * @package amazon-bundle
 * @author John Pancoast
 * @date 2013-03-20
 * @copyright (c) 2013 Underground Elephant
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

use Uecode\Bundle\AmazonBundle\Command\SimpleWorkflow\SDKCommandCommand;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

/**
 * List open workflow executions
 */
class ListOpenWorkflowExecutionsCommand extends SDKCommandCommand
{
	/**
	 * @var string command we call
	 *
	 * Here because call_open_workflow_executions and list_open_workflow_connections are similar
	 *
	 * @access protected
	 */
	protected $command = 'list_open_workflow_executions';

	/**
	 * @var string command name
	 *
	 * Here because call_open_workflow_executions and list_open_workflow_connections are similar
	 *
	 * @access protected
	 */
	protected $name = 'ue:aws:swf:list_open_workflow_executions';

	/**
	 * @var string command description
	 *
	 * Here because call_open_workflow_executions and list_open_workflow_connections are similar
	 *
	 * @access protected
	 */
	protected $description = 'Call ListOpenWorkflowExecutions SWF API call. --domain and --oldest_date are required.';

	/*
	 * inherit
	 */
	protected function configure() {
		$this
			->setName($this->name)
			->setDescription($this->description)
			->addOption(
				'domain',
				'd',
				InputOption::VALUE_REQUIRED,
				'See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_ListOpenWorkflowExecutions.html'
			)
			->addOption(
				'workflow_id',
				'w',
				InputOption::VALUE_REQUIRED,
				'See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_ListOpenWorkflowExecutions.html'
			)
			->addOption(
				'latest_date',
				'l',
				InputOption::VALUE_REQUIRED,
				'http://docs.aws.amazon.com/amazonswf/latest/apireference/API_ListOpenWorkflowExecutions.html'
			)
			->addOption(
				'oldest_date',
				'o',
				InputOption::VALUE_REQUIRED,
				'oldestDate. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_ListOpenWorkflowExecutions.html'
			)
			->addOption(
				'tag_filter',
				't',
				InputOption::VALUE_REQUIRED,
				'See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_ListOpenWorkflowExecutions.html'
			)
			;
	}

	/*
	 * inherit
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$domain = $input->getOption('domain');
		$workflowId = $input->getOption('workflow_id');
		$oldestDate = $input->getOption('oldest_date');
		$latestDate = $input->getOption('latest_date');
		$tagFilter = $input->getOption('tag_filter');

		if (!$domain) {
			throw new \Exception('Must pass --domain option.');
		}

		if (!$oldestDate) {
			throw new \Exception('Must pass --oldest_date option.');
		}

		$options = array(
			'domain' => $domain,
			'startTimeFilter' => array(
				'oldestDate' => (int)$oldestDate
			)
		);

		if ($workflowId) {
			$options['executionFilter']['workflowId'] = $workflowId;
		}
		if ($latestDate) {
			$options['startTimeFilter']['latestDate'] = (int)$latestDate;
		}
		if ($tagFilter) {
			$options['tagFilter']['tag'] = $tagFiler;
		}

		$output->writeln(print_r($this->callCommand($options)->body, true));
	}

	/**
	 * Call our SDK command.
	 *
	 * Mainly here for CountOpenWorkfloExecutions and ListOpenWorkflowExecutions (both are similar in options).
	 *
	 * @param array $options
	 * @access protected
	 * @final
	 */
	protected function callCommand(array $options) {
		return $this->callSDKCommand($this->command, $options);
	}
}
