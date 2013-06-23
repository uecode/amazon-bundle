<?php

/**
 * @package amazon-bundle
 * @author Aaron Scherer
 * @copyright (c) 2013 Undeground Elephant
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

namespace Uecode\Bundle\AmazonBundle\Component;

// Exceptions
//use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
//use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Amazon Bundle Components
use \Uecode\Bundle\AmazonBundle\Component\AbstractAmazonComponent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\ActivityWorker;

/**
 * For working w/ Amazon SWF
 *
 * @copyright (c) 2013 Underground Elepahant
 * @author John Pancoast
 */
class SimpleWorkflow extends AbstractAmazonComponent
{
	/*
	 * inherit
	 * @return \AmazonSWF
	 */
	public function buildAmazonObject(array $options)
	{
		return new \AmazonSWF($options);
	}

	/**
	 * Build a decider
	 *
	 * @access public
	 * @param string $name Workflow name used for registration
	 * @param string $workflowVersion Workflow version used for egistration and finding decider related classes.
	 * @param string $activityVersion Activity version used for activity registration and finding activity related classes.
	 * @param string $taskList Task list to poll on
	 * @return DeciderWorker
	 */
	public function buildDecider($name, $workflowVersion, $activityVersion, $taskList)
	{
		return new DeciderWorker($this, $name, $workflowVersion, $activityVersion, $taskList);
	}

	/**
	 * Build and run a decider
	 *
	 * @access public
	 * @param string $name Workflow name used for registration
	 * @param string $workflowVersion Workflow version used for egistration and finding decider related classes.
	 * @param string $activityVersion Activity version used for activity registration and finding activity related classes.
	 * @param string $taskList Task list to poll on
	 */
	public function runDecider($name, $workflowVersion, $activityVersion, $taskList)
	{
		$b = $this->buildDecider($name, $workflowVersion, $activityVersion, $taskList);
		$b->run();
	}


	/**
	 * Build an activity worker
	 *
	 * @access public
	 * @param string $taskList Task list to poll on
	 * @param string $identity Identity of this activity worker (recorded in ActivityTaskStarted event)
	 * @return ActivityWorker
	 */
	public function buildActivityWorker($taskList, $identity = null)
	{
		return new ActivityWorker($this, $taskList, $identity);
	}

	/**
	 * Build and run activity worker
	 *
	 * @access public
	 * @param string $taskList Task list to poll on
	 * @param string $identity Identity of this activity worker (recorded in ActivityTaskStarted event)
	 */
	public function runActivityWorker($taskList, $identity = null)
	{
		$b = $this->buildActivityWorker($taskList, $identity);
		$b->run();
	}

	#########################################
	## SDK ABSTRACTIONS #####################
	#########################################

	/**
	 * Wrapper for SDK PollForDecisionTask
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function pollForDecisionTask(array $options = array())
	{
		return $this->getAmazonObject()->poll_for_decision_task($options);
	}

	/**
	 * Wrapper for SDK RespondDecisionTaskCompleted
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function respondDecisionTaskCompleted(array $options = array())
	{
		return $this->getAmazonObject()->respond_decision_task_completed($options);
	}

	/**
	 * Wrapper for SDK PollForActivityTask
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function pollForActivityTask(array $options = array())
	{
		return $this->getAmazonObject()->poll_for_activity_task($options);
	}

	/**
	 * Wrapper for SDK RespondActivityTaskCompleted
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function respondActivityTaskCompleted(array $options = array())
	{
		return $this->getAmazonObject()->respond_activity_task_completed($options);
	}

	/**
	 * Wrapper for SDK RespondActivityTaskCanceled
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function respondActivityTaskCanceled(array $options = array())
	{
		return $this->getAmazonObject()->respond_activity_task_canceled($options);
	}

	/**
	 * Wrapper for SDK RespondActivityTaskFailed
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function respondActivityTaskFailed(array $options = array())
	{
		return $this->getAmazonObject()->respond_activity_task_failed($options);
	}

	/**
	 * Wrapper for SDK RegisterWorkflowType
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function registerWorkflowType(array $options = array())
	{
		return $this->getAmazonObject()->register_workflow_type($options);
	}

	/**
	 * Wrapper for SDK DescribeWorkflowType
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function describeWorkflowType(array $options = array())
	{
		return $this->getAmazonObject()->describe_workflow_type($options);
	}

	/**
	 * Wrapper for SDK RegisterActivityType
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function registerActivityType(array $options = array())
	{
		return $this->getAmazonObject()->register_activity_type($options);
	}

	/**
	 * Wrapper for SDK ListOpenWorkflowExecutions
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function listOpenWorkflowExecutions(array $options = array())
	{
		return $this->getAmazonObject()->list_open_workflow_executions($options);
	}

	/**
	 * Wrapper for SDK CountOpenWorkflowExecutions
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function countOpenWorkflowExecutions(array $options = array())
	{
		return $this->getAmazonObject()->count_open_workflow_executions($options);
	}

	/**
	 * Wrapper for SDK TerminateWorkflowExecution
	 *
	 * @param array $options
	 * @return CFResponse
	 * @throws \Exception (TODO what is actual exception, lazy?)
	 */
	public function terminateWorkflowExecution(array $options = array())
	{
		return $this->getAmazonObject()->terminate_workflow_executions($options);
	}
}