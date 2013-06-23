<?php

/**
 * Common class for decider and activity workers
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

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow;

// Amazon components
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\Util;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

// Symfony and related
use Monolog\Logger;

class Worker
{
	/**
	 * @var SimpleWorkflow
	 *
	 * @access protected
	 */
	protected $swf;

	/**
	 * @var \CFResponse A response from either PollForDecisionTask or PollForActivityTask (depends on context)
	 *
	 * @access protected
	 */
	protected $response;

	/**
	 * @var string The SWF domain this worker is working in.
	 *
	 *  @access protected
	 */
	protected $domain;

	/**
	 * @var int This workers process id.
	 *
	 * @access private
	 */
	private $processId;

	/**
	 * @var int An id representing this execution
	 *
	 * This id is created locally and remains the same through the entire execution of this file.
	 *
	 * @access  private
	 */
	private $executionId;

	/**
	 * @var int Amazon created run id.
	 *
	 * This id is created by amazon when a workflow is started and is passed in the
	 * response from PollForDecisionTask.
	 *
	 * @access  protected
	 */
	protected $amazonRunId;

	/**
	 * @var int User created workflow id.
	 *
	 * This id is createde by the client that started the workflow and is passed in the
	 * response from PollForDecisionTask.
	 *
	 * @access protected
	 */
	protected $amazonWorkflowId;

	/**
	 * @var bool Do we run another loop of work.
	 */
	private $doRun = true;

	/**
	 * Constructor
	 *
	 * @param AmazonSWF $swf An instance of the main amazon class
	 * @param string The SWF domain this worker is working in
	 * @access protected
	 */
	protected function __construct(SimpleWorkflow $swf)
	{
		$this->registerSignalHandlers();
		$this->setSWFObject($swf);

		// TODO this should be reset each worker loop
		$this->executionId = Util::generateUUID();
	}

	/**
	 * Register our system signal handlers
	 *
	 * @access private
	 */
	private function registerSignalHandlers() {
		pcntl_signal(SIGTERM, array($this, 'signalHandler'));
		pcntl_signal(SIGHUP, array($this, 'signalHandler'));
		pcntl_signal(SIGINT, array($this, 'signalHandler'));
	}

	/**
	 * Log a worker related event
	 *
	 * @access public
	 * @param string $level Log level {@see Monolog\Logger::log()}.
	 * @param string $message Your message
	 * @param mixed $context Additional log info
	 */
	public function log($level, $message, $context = null) {
		$this->getLogger()->log($level, $message, $this->getLogContext($context));
	}

	/**
	 * Get an array suitable for using as monolog
	 * context for decider and activity workers.
	 *
	 * @access protected
	 * @param mixed $data Additional data.
	 * @return array
	 * @uses getLogContextStatic
	 */
	protected function getLogContext($data = null) {
		$class = basename(str_replace('\\', '/', get_class($this)));
		if ($class == 'ActivityWorker') {
			$workerType = 'a';
		} elseif ($class == 'DeciderWorker') {
			$workerType = 'd';
		} else {
			$workerType = 'u';
		}

		return self::getLogContextStatic(
			$workerType,
			getmypid(),
			$this->getExecutionId(),
			$this->getAmazonRunId(),
			$this->getAmazonWorkflowId(),
			$data
		);
	}

	/**
	 * Get an array suitable for using as monolog
	 * context for decider and activity workers.
	 *
	 * @param string $workerType Either 'd' for decider or a for'activity'
	 * @param string $processId Process ID of process that's logging.
	 * @param string $executionId Our execution id
	 * @param string $amazonRunId Amazon's run id
	 * @param string $amazonWorkflowId Amazon's workflow id
	 * @param mixed $data Additional data.
	 * @return array
	 */
	public static function getLogContextStatic($workerType, $processId, $executionId, $amazonRunId = null, $amazonWorkflowId = null, $data = null)
	{
		return array(
			'worker' => true,
			'workerType' => ($workerType == 'd' || $workerType == 'a') ? $workerType : 'unknown',
			'host' => gethostname(),
			'processId' => $processId,
			'executionId' => $executionId,
			'runId' => $amazonRunId,
			'workflowId' => $amazonWorkflowId,
			'data' => $data
		);
	}

	/**
	 * Do we run another loop of work
	 *
	 * @access protected
	 * @return bool
	 */
	protected function doRun() {
		// calls self::signalHandler()
		pcntl_signal_dispatch();

		return (bool)$this->doRun;
	}

	/**
	 * System signal handler
	 *
	 * You normally won't call this dilrectly.
	 *
	 * @access public
	 * @param int $signal One of the system related constants at {@see http://www.php.net/manual/en/pcntl.constants.php}
	 */
	public function signalHandler($signal) {
		switch ($signal) {
			// stop related signals
			case SIGTERM:
			case SIGHUP:
			case SIGINT:
				$this->log(
					'info',
					'Worker received stop signal'
				);
				$this->doRun = false;
				break;
		}
	}

	/**
	 * Get the current execution id
	 *
	 * @access  public
	 * @return string
	 */
	public function getExecutionId()
	{
		return $this->executionId;
	}

	/**
	 * Get our process id
	 *
	 * @access public
	 * @return int
	 */
	public function getProcessId() {
		if (!$this->processId) {
			$this->processId = (int)getmypid();
		}
		return $this->processId;
	}

	/**
	 * Set the amazon run id
	 *
	 * @param string $id
	 * @access protected
	 */
	protected function setAmazonRunId($id)
	{
		$this->amazonRunId = $id;
	}

	/**
	 * Get the amazon run id
	 *
	 * @access public
	 * @return string
	 */
	public function getAmazonRunId()
	{
		return $this->amazonRunId;
	}

	/**
	 * Set the amazon workflow id
	 *
	 * @param string $id
	 * @access protected
	 */
	protected function setAmazonWorkflowId($id)
	{
		$this->amazonWorkflowId = $id;
	}

	/**
	 * Get the amazon workflow id
	 *
	 * @access public
	 * @return string
	 */
	public function getAmazonWorkflowId()
	{
		return $this->amazonWorkflowId;
	}

	/**
	 * Get a workflow array from config
	 *
	 * You can pass any (or no) combination of name and version to find that set.
	 *
	 * @final
	 * @access protected
	 * @param string $name The workflowType name
	 * @param mixed $version The workflowType version
	 * @return array
	 */
	final public function getWorkflowConfig($name = null, $version = null)
	{
		$ret = array();

		$wf = $this->getSWFObject()->getConfig()->get('simpleworkflow');

		foreach ($wf['domains'] as $dk => $dv) {
			if ($this->domain == $dk) {
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
	 * @param string $name The activityType name
	 * @param mixed $version The activityType version
	 * @return array
	 */
	final public function getActivityConfig($name = null, $version = null)
	{
		$ret = array();

		$wf = $this->getSWFObject()->getConfig()->get('simpleworkflow');

		foreach ($wf['domains'] as $dk => $dv) {
			if ($this->domain == $dk) {
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
	final public function getEventNamespace($name, $version) {
		$cfg = $this->getWorkflowConfig($name, $version);
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
	final public function getActivityEventNamespace($name, $version) {
		$cfg = $this->getWorkflowConfig($name, $version);
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
	final public function getActivityClass($name, $version)
	{
		$cfg = $this->getActivityConfig($name, $version);
		if (count($cfg) != 1 || !isset($cfg[0]['class'])) {
			return;
		}

		return $cfg[0]['class'];
	}

	/**
	 * Get the response the worker is currently working w/
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * @param AmazonSWF $amazonObject
	 *
	 * @access public
	 * @param SimpleWorkflow
	 * @return Worker
	 */
	public function setSWFObject(SimpleWorkflow $swfObject)
	{
		$this->swf = $swfObject;
		return $this;
	}

	/**
	 * @return Simpleworkflow
	 */
	public function getSWFObject()
	{
		return $this->swf;
	}

	/**
	 * @return AmazonSWF
	 */
	public function getAmazonObject()
	{
		return $this->getSWFObject()->getAmazonObject();
	}

	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->getSWFObject()->getLogger();
	}
}