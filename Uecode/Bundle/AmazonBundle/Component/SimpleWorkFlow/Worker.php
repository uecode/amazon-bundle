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

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

// Amazon components
use \Uecode\Bundle\AmazonBundle\Component\AmazonComponent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Util;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

// Symfony and related
use Monolog\Logger;

class Worker extends AmazonComponent
{
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
	 * @var Logger Logger instance
	 *
	 * @access protected
	 */
	protected $logger;

	/**
	 * @var bool Do we run another loop of work.
	 */
	private $doRun = true;

	/**
	 * @var string Workflow type version used for registration and for finding location of decider related classes.
	 *
	 * @access protected
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-version
	 */
	protected $workflowVersion;

	/**
	 * @var string Activity version used for activity registration and finding activity related classes.
	 *
	 * @access protected
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterActivityType.html#SWF-RegisterActivityType-request-version
	 */
	protected $activityVersion;

	/**
	 * Constructor
	 *
	 * @param AmazonSWF $swf An instance of the main amazon class
	 * @access protected
	 */
	protected function __construct(AmazonSWF $swf)
	{
		$this->executionId = Util::generateUUID();
		$this->registerSignalHandlers();
		$this->setAmazonClass($swf);
		$this->setLogger($swf->getLogger());
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
	 * Set the logger
	 *
	 * @access protected
	 * @param Logger $logger
	 */
	protected function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Get the logger
	 *
	 * @access protected
	 * @return Logger
	 */
	protected function getLogger()
	{
		return $this->logger;
	}

	/**
	 * Simple helper to get activity array out of config
	 *
	 * @final
	 * @access protected
	 * @return array
	 */
	final protected function getActivityArray()
	{
		$config = $this->amazonClass->getConfig();
		$wf = $config->get('simpleworkflow');
		$domain = $config->get('domain');

		foreach ($wf['domains'] as $dk => $dv) {
			if ($domain == $dk) {
				foreach ($dv['activities'] as $a) {
					if ($a['version'] == $this->activityVersion) {
						return $a;
					}
				}
			}
		}

		return array();
	}

	/**
	 * Simple helper to get activity directory out of config
	 *
	 * @final
	 * @access protected
	 * @return string
	 */
	final protected function getActivityDirectory()
	{
		$ar = $this->getActivityArray();
		return $ar['directory'];
	}

	/**
	 * Simple helper to get activity namespace out of config
	 *
	 * @final
	 * @access protected
	 */
	final protected function getActivityNamespace()
	{
		$ar = $this->getActivityArray();
		return $ar['namespace'];
	}
}