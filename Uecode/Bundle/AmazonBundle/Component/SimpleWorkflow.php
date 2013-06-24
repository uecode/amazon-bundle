<?php

/**
 * @package amazon-bundle
 * @author John Pancoast, Aaron Scherer
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
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\AbstractActivity;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

/**
 * For working w/ Amazon SWF
 *
 * @author John Pancoast, Aaron Scherer
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
	 * @param string $domain Domain to poll on
	 * @param string $name Workflow type to poll on
	 * @param string $taskList Tasklist to poll on
	 * @return DeciderWorker
	 */
	public function buildDecider($domain, $name, $taskList)
	{
		return new DeciderWorker($this, $domain, $name, $taskList);
	}

	/**
	 * Build and run a decider
	 *
	 * @access public
	 * @param string $name Workflow name used for registration
	 * @param string $taskList Task list to poll on
	 */
	public function runDecider($domain, $name, $taskList)
	{
		$b = $this->buildDecider($domain, $name, $taskList);
		$b->run();
	}

	/**
	 * Build an activity worker
	 *
	 * @access public
	 * @param string $domain Domain to poll on
	 * @param string $taskList Task list to poll on
	 * @return ActivityWorker
	 */
	public function buildActivityWorker($domain, $taskList)
	{
		return new ActivityWorker($this, $domain, $taskList);
	}

	/**
	 * Build and run activity worker
	 *
	 * @access public
	 * @param string $domain Domain to poll on
	 * @param string $taskList Tasklist to poll on
	 */
	public function runActivityWorker($domain, $taskList)
	{
		$b = $this->buildActivityWorker($domain, $taskList);
		$b->run();
	}

	/**
	 * Registers a workflow.
	 *
	 * You can pass any variation of $name and $version to find matches.
	 *
	 * @final
	 * @access public
	 * @param string $domain Workflow domain
	 * @param string $name Workflow name
	 * @param string $version Workflow version
	 */
	final public function registerWorkflow($domain, $name = null, $version = null)
	{
		$cfg = $this->getWorkflowConfig($domain, $name, $version);

		if (empty($cfg)) {
			throw new \Exception("Workflow is not configured [domain: $domain, workflow type: $name, version: $version]");
		}

		foreach ($cfg as $c) {
			$registerRequest = array(
				'domain' => $domain,
				'name' => $c['name'],
				'version' => (string)$c['version']
			);

			if (isset($c['defaultChildPolicy'])) {
				$registerRequest['defaultChildPolicy'] = $c['defaultChildPolicy'];
			}

			if (isset($c['defaultTaskList'])) {
				$registerRequest['defaultTaskList'] = array('name' => $c['defaultTaskList']);
			}

			if (isset($c['defaultTaskStartToCloseTimeout'])) {
				$registerRequest['defaultTaskStartToCloseTimeout'] = (string)$c['defaultTaskStartToCloseTimeout'];
			}

			if (isset($c['defaultExecutionStartToCloseTimeout'])) {
				$registerRequest['defaultExecutionStartToCloseTimeout'] = (string)$c['defaultExecutionStartToCloseTimeout'];
			}

			$response = $this->registerWorkflowType($registerRequest);

			$this->log(
				'info',
				'Registering workflow',
				array(
					'request' => $registerRequest,
					'response' => json_decode(json_encode($response), true)
				)
			);

			if (!$response->isOK() && $response->body->__type != 'com.amazonaws.swf.base.model#TypeAlreadyExistsFault') {
				throw new \Exception('Could not register workflow');
			}
		}
	}

	/**
	 * Registers activities
	 *
	 * You can pass any variation of $name and $version to find matches.
	 *
	 * @final
	 * @access public
	 * @param string $domain The activity domain
	 * @param string $name The activity name to register
	 * @param string $version The version of the activity to registerof activities to register
	 */
	final public function registerActivities($domain, $name = null, $version = null)
	{
		$cfg = $this->getActivityConfig($domain, $name, $version);

		if (empty($cfg)) {
			$this->log(
				'info',
				'Attempting to register activities but no activities in config'
			);

			return;
		}

		foreach ($cfg as $a) {
			$this->log(
				'debug',
				'Attempting to register activity',
				array('class' => $a['class'])
			);

			if (!class_exists($a['class'])) {
				throw new InvalidConfigurationException('Cannot find activity class to register @ '.$a['class']);
			}

			$obj = new $a['class'];

			if (!($obj instanceof AbstractActivity)) {
				throw new InvalidClassException('Found activity '.$a['class'].' but it is not an instance of AbstractActivity');
			}

			$registerRequest = array(
				'domain' => $domain,
				'name' => $a['name'],
				'version' => (string)$a['version']
			);

			if ($a['default_task_list']) {
				$registerRequest['defaultTaskList'] = array('name' => $a['default_task_list']);
			}

			// TODO add other registration key/value pairs here

			// register type (ignoring "already exists" fault for now)
			$response = $this->registerActivityType($registerRequest);

			$this->log(
				'debug',
				'RegisterActivityType',
				array(
					'request' => $registerRequest,
					'response' => $response
				)
			);

			if (!$response->isOK() && $response->body->__type != 'com.amazonaws.swf.base.model#TypeAlreadyExistsFault') {
				$this->log(
					'alert',
					'Could not register activity',
					array(
						'trace' => debug_backtrace()
					)
				);

				exit;
			}
		}
	}

	/**
	 * Get a workflow array from config
	 *
	 * You can pass any (or no) combination of name and version to find that set.
	 *
	 * @final
	 * @access protected
	 * @param string $domain The workfloeType domain
	 * @param string $name The workflowType name
	 * @param mixed $version The workflowType version
	 * @return array
	 */
	final public function getWorkflowConfig($domain, $name = null, $version = null)
	{
		$ret = array();

		$wf = $this->getConfig()->get('simpleworkflow');

		foreach ($wf['domains'] as $dk => $dv) {
			if ($domain == $dk) {
				if (!$name && !$version) {
					$ret = $dv['workflows'];
					continue;
				}

				foreach ($dv['workflows'] as $a) {
					if (($name && $version && $name == $a['name'] && $version == $a['version'])
					 || ($name && !$version && $name == $a['name'])
					 || (!$name && $version && $version == $a['version'])) {
						$ret[] = $a;
					}
				}
			}
		}

		return $ret;
	}


	/**
	 * Get activity array out of config
	 *
	 * You can pass any (or no) combination of name and version to find that set.
	 *
	 * @final
	 * @access protected
	 * @param string $domain The activityType domain
	 * @param string $name The activityType name
	 * @param mixed $version The activityType version
	 * @return array
	 */
	final public function getActivityConfig($domain, $name = null, $version = null)
	{
		$ret = array();

		$wf = $this->getConfig()->get('simpleworkflow');

		foreach ($wf['domains'] as $dk => $dv) {
			if ($domain == $dk) {
				if (!$name && !$version) {
					$ret = $dv['activities'];
					continue;
				}

				foreach ($dv['activities'] as $a) {
					if (($name && $version && $name == $a['name'] && $version == $a['version'])
					 || ($name && !$version && $name == $a['name'])
					 || (!$name && $version && $version == $a['version'])) {
						$ret[] = $a;
					}
				}
			}
		}

		return $ret;
	}

	/**
	 * Find event namespace from workflow config section
	 *
	 * @final
	 * @access protected
	 * @param string $name The workfloeType name
	 * @param mixed $version The workflowType version
	 * @return string
	 */
	final public function getEventNamespace($domain, $name, $version) {
		$cfg = $this->getWorkflowConfig($domain, $name, $version);
		if (count($cfg) != 1 || !isset($cfg[0]['history_event_namespace'])) {
			return;
		}

		return $cfg[0]['history_event_namespace'];
	}

	/**
	 * Find activity event namespace from workflow config section 
	 *
	 * @final
	 * @access protected
	 * @param string $name The workfloeType name
	 * @param mixed $version The workflowType version
	 * @return string
	 */
	final public function getActivityEventNamespace($domain, $name, $version) {
		$cfg = $this->getWorkflowConfig($domain, $name, $version);
		if (count($cfg) != 1 || !isset($cfg[0]['history_activity_event_namespace'])) {
			return;
		}

		return $cfg[0]['history_activity_event_namespace'];
	}

	/**
	 * Get activity namespace from activity config section
	 *
	 * @final
	 * @param string $name activityType name
	 * @param mixed $version activitytype version
	 * @access protected
	 * @return string
	 */
	final public function getActivityClass($domain, $name, $version)
	{
		$cfg = $this->getActivityConfig($domain, $name, $version);
		if (count($cfg) != 1 || !isset($cfg[0]['class'])) {
			return;
		}

		return $cfg[0]['class'];
	}

	/**
	 * Log an event
	 *
	 * @access public
	 * @param string $level Log level {@see Monolog\Logger::log()}.
	 * @param string $message Your message
	 * @param array $context Additional log info
	 */
	public function log($level, $message, $context = array()) {
		$this->getLogger()->log($level, $message, $context);
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