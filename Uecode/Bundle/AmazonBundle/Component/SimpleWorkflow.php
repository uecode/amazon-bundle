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

// Amazon Bundle Components
use \Uecode\Bundle\AmazonBundle\Component\AbstractAmazonComponent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\ActivityWorker;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\AbstractActivityTask;

/**
 * For working w/ Amazon SWF
 *
 * @author John Pancoast, Aaron Scherer
 */
class SimpleWorkflow extends AmazonComponent
{
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
	 * Registers everything
	 *
	 * @final
	 * @access public
	 * @param string $domain Workflow domain
	 */
	final public function registerAll()
	{
		// this is terribly innefficient looping through these configs like these calls
		// will do but it's only a problem during registration calls which are
		// not important to the core functionality of this framework.
		// That said, it should be fixed.
		foreach ($this->getDomainConfig() as $d) {
			$this->registerDomains($d['name']);
			$this->registerWorkflows($d['name']);
			$this->registerActivities($d['name']);
		}
	}

	/**
	 * Registers domain(s)
	 *
	 * @final
	 * @access public
	 * @param string $domain Workflow domain
	 */
	final public function registerDomains($domain = null)
	{
		$cfg = $this->getDomainConfig($domain);

		if (empty($cfg)) {
			$this->log(
				'warning',
				'Attempting to register domains but nothing found',
				array(
					'domain' => $domain,
				)
			);

			return;
		}

		foreach ($cfg as $d) {
			$registerRequest = array(
				'name' => $d['name'],
				'workflowExecutionRetentionPeriodInDays' => (string)$d['workflow_execution_retention_period']
			);

			if (isset($d['description'])) {
				$registerRequest['description'] = $d['description'];
			}

			$response = $this->callSDK('RegisterDomain', $registerRequest);

			$this->log(
				'info',
				'Registering domain',
				array(
					'request' => $registerRequest,
					'response' => json_decode(json_encode($response), true)
				)
			);

			if (!$response->isOK() && $response->body->__type != 'com.amazonaws.swf.base.model#DomainAlreadyExistsFault') {
				throw new \Exception('Could not register domain');
			}
		}
	}

	/**
	 * @see self::registerDomains()
	 */
	final public function registerDomain($domain = null) {
		return $this->registerDomains($domain);
	}

	/**
	 * Registers workflow(s)
	 *
	 * You can pass any variation of $name and $version to find matches.
	 *
	 * @final
	 * @access public
	 * @param string $domain Workflow domain
	 * @param string $name Workflow name
	 * @param string $version Workflow version
	 */
	final public function registerWorkflows($domain, $name = null, $version = null)
	{
		$cfg = $this->getWorkflowConfig($domain, $name, $version);

		if (empty($cfg)) {
			$this->log(
				'warning',
				'Attempting to register workflows but no workflows in config',
				array(
					'domain' => $domain,
					'name' => $name,
					'version' => $version
				)
			);

			return;
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

			$response = $this->callSDK('RegisterWorkflowType', $registerRequest);

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
	 * @see self::registerWorkflows()
	 */
	final public function registerWorkflow($domain, $name = null, $version = null) {
		return $this->registerWorkflows($domain, $name, $version);
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
				'warning',
				'Attempting to register activities but no activities in config',
				array(
					'domain' => $domain,
					'name' => $name,
					'version' => $version
				)
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

			if (!($obj instanceof AbstractActivityTask)) {
				throw new InvalidClassException('Found activity '.$a['class'].' but it must extend AbstractActivityTask.');
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
			$response = $this->callSDK('RegisterActivityType', $registerRequest);

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

	final public function getDomainConfig($domain = null)
	{
		$ret = array();

		foreach ($this->getConfig()->get('simpleworkflow')['domains'] as $dk => $dv) {
			if ($domain == $dk || !$domain) {
				$dv['name'] = $dk;
				$ret[] = $dv;
			}
		}

		return $ret;
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
			if (isset($dv['workflows']) && !empty($dv['workflows']) && ($domain == $dk || !$domain)) {
				if (!$name && !$version) {
					$ret = array_merge($ret, $dv['workflows']);
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
			if ($domain == $dk || !$domain) {
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
	 * Find class for the decider of this workflow
	 *
	 * @final
	 * @access protected
	 * @param string $domain The domain
	 * @param string $name The workfloeType name
	 * @param mixed $version The workflowType version
	 * @return string
	 */
	final public function getDeciderClass($domain, $name, $version) {
		$cfg = $this->getWorkflowConfig($domain, $name, $version);
		if (count($cfg) != 1 || !isset($cfg[0]['class'])) {
			return;
		}

		return $cfg[0]['class'];
	}

	/**
	 * Alias of {@see self::getDeciderClass()}
	 *
	 * @final
	 * @access protected
	 * @param string $domain The domain
	 * @param string $name The workfloeType name
	 * @param mixed $version The workflowType version
	 * @return string
	 */
	final public function getWorkflowClass($domain, $name, $version) {
		return $this->getDeciderClass($domain, $name, $version);
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
}
