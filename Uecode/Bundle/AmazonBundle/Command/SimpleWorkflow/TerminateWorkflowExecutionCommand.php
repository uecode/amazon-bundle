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
 * Terminate a workflow execution.
 */
class TerminateWorkflowExecutionCommand extends SDKCommandCommand
{
	/*
	 * inherit
	 */
	protected function configure() {
		$this
			->setName('ue:aws:swf:terminate_workflow_execution')
			->setDescription('Terminate a workflow execution. You must pass at least a --domain and --workflow_id.')
			->addOption(
				'child_policy',
				'c',
				InputOption::VALUE_REQUIRED,
				'http://docs.aws.amazon.com/amazonswf/latest/apireference/API_TerminateWorkflowExecution.html'
			)
			->addOption(
				'details',
				't',
				InputOption::VALUE_REQUIRED,
				'oldestDate. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_TerminateWorkflowExecution.html'
			)
			->addOption(
				'domain',
				'd',
				InputOption::VALUE_REQUIRED,
				'See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_TerminateWorkflowExecution.html'
			)
			->addOption(
				'reason',
				'o',
				InputOption::VALUE_REQUIRED,
				'See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_TerminateWorkflowExecution.html'
			)
			->addOption(
				'run_id',
				'r',
				InputOption::VALUE_REQUIRED,
				'http://docs.aws.amazon.com/amazonswf/latest/apireference/API_TerminateWorkflowExecution.html'
			)
			->addOption(
				'workflow_id',
				'w',
				InputOption::VALUE_REQUIRED,
				'oldestDate. See http://docs.aws.amazon.com/amazonswf/latest/apireference/API_TerminateWorkflowExecution.html'
			)
			;
	}

	/*
	 * inherit
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$childPolicy = $input->getOption('child_policy');
		$details = $input->getOption('details');
		$domain = $input->getOption('domain');
		$reason = $input->getOption('reason');
		$runId = $input->getOption('run_id');
		$workflowId = $input->getOption('workflow_id');

		if (!$domain) {
			throw new \Exception('Must pass --domain option.');
		}

		if (!$workflowId) {
			throw new \Exception('Must pass --workflow_id option.');
		}

		$options = array(
			'domain' => $domain,
			'workflowId' => $workflowId,
		);

		if ($childPolicy) {
			$options['childPolicy'] = $childPolicy;
		}
		if ($details) {
			$options['details'] = $details;
		}
		if ($reason) {
			$options['reason'] = $reason;
		}
		if ($runId) {
			$options['runId'] = $runId;
		}

		$output->writeln(print_r($this->callSDKCommand('terminateWorkflowExecution', $options)->body, true));
	}
}
