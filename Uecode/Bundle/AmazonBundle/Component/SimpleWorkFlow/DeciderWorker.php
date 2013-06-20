<?php

/**
 * Base logic for Amazon SWF decider
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author Aaron Scherer, John Pancoast
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

// Amazon Components
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\HistoryEventIterator;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\State\DeciderWorkerState;
use \Uecode\Bundle\AmazonBundle\Model\SimpleWorkflow;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Worker;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Events
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\AbstractHistoryEvent;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

class DeciderWorker extends Worker
{
	/**
	 * @var \CFResponse
	 */
	private $workflow;

	/**
	 * @var string Workflow name used for registration
	 *
	 * @access private
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-name
	 */
	private $name;

	/**
	 * @var string Workflow default child policy. sent in registration.
	 *
	 * @access private
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultChildPolicy
	 */
	private $defaultChildPolicy;

	/**
	 * @var string Workflow default tasklist. sent in registration.
	 *
	 * @access private
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultTaskList
	 */
	private $defaultTaskList;

	/**
	 * @var int Default task execution to close timeout. Sent in registration.
	 *
	 * @access private
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultTaskStartToCloseTimeout
	 */
	private $defaultTaskStartToCloseTimeout;

	/**
	 * @var int Default task start to close timeout. Sent in registration.
	 *
	 * @access private
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-defaultExecutionStartToCloseTimeout
	 */
	private $defaultExecutionStartToCloseTimeout;

	/**
	 * @var string Task list to poll on
	 *
	 * @access private
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html#SWF-PollForDecisionTask-request-taskList
	 */
	private $taskList;

	/** 
	 * @var string Namespace where your workflow's history events are located.
	 *
	 * @access private
	 */
	private $eventNamespace;

	/**
	 * @var string Namespace where your workflow's history activity events are located.
	 */
	private $activityNamespace;

	/**
	 * @var array Holds events in history (can be used for task lookup)
	 *
	 * @access protected
	 */
	protected $events = array();

	/**
	 * Builds the Workflow
	 *
	 * @final
	 * @access public
	 * @param \AmazonSWF $swf
	 * @param array $workflowType
	 * @param string $domain Domain name to register workflow in
	 * @param string $name Workflow name used for registration
	 * @param string $workflowVersion Workflow version used for egistration and finding decider related classes.
	 * @param string $activityVersion Activity version used for activity registration and finding activity related classes.
	 * @param string $taskList Task list to poll on
	 */
	final public function __construct(AmazonSWF $swf, $domain, $name, $workflowVersion, $activityVersion, $taskList) {
		parent::__construct($swf);

		$cfg = $swf->getConfig()->get('simpleworkflow');

		// did we find a match in config
		$match = false;

		if (isset($cfg['domains'])) {
			foreach ($cfg['domains'] as $dk => $dv) {
				if ($dk == $domain) {
					if (!isset($dv['workflows'])) {
						continue;
					}

					foreach ($dv['workflows'] as $w) {
						if ($w['name'] == $name && $w['version'] == $workflowVersion) {
							$match = true;

							$this->domain = $domain;
							$this->name = $name;
							$this->workflowVersion = $workflowVersion;
							$this->activityVersion = $activityVersion;
							$this->taskList = $taskList;
							$this->defaultChildPolicy = $w['default_child_policy'];;
							$this->defaultTaskList = $w['default_task_list'];
							$this->defaultTaskStartToCloseTimeout = isset($w['default_task_timeout']) ? $w['default_task_timeout'] : null;
							$this->defaultExecutionStartToCloseTimeout = isset($w['default_execution_timeout']) ? $w['default_execution_timeout'] : null;
							$this->eventNamespace = $w['history_event_namespace'];
							$this->activityNamespace = $w['history_activity_event_namespace'];
						}
					}
				}
			}
		}

		if (!$match) {
			throw new \Exception("Decider is not configured [domain: $domain, workflow type: $name, version: $version]");
		}

		$this->registerWorkflow();
		$this->registerActivities();
	}

	/**
	 * Run the decider worker.
	 *
	 * This will make a request to amazon (a long poll) waiting for a decider task
	 * to perform. If amazon doesn't respond within a minute, they'll send an empty
	 * response and we'll start another loop. If they respond with a decision task
	 * we'll further process that {@see self::decide()}.
	 *
	 * @access public
	 * @final
	 * @uses self::decide()
	 */
	final public function run()
	{
		$this->log(
			'info',
			'Starting decider worker polling'
		);

		try {
			// run until we receive a signal to stop
			while ($this->doRun()) {
				// these values can only be set from amazon response
				$this->setAmazonRunId(null);
				$this->setAmazonWorkflowId(null);

				// poll amazon for decision task and handle if successful
				// http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html
				$pollRequest = array(
					'domain' => $this->domain,
					'taskList' => array('name' => $this->taskList)
				);

				$response = $this->amazonClass->poll_for_decision_task($pollRequest);

				$this->log(
					'debug',
					'PollForDecisionTask',
					array(
						'request' => $pollRequest,
						'response' => json_decode(json_encode($response), true)
					)
				);

				if ($response->isOK()) {
					// unique id for this task which is used when we make our RespondDecisionTaskCompleted call.
					$taskToken = (string)$response->body->taskToken;

					if (!empty($taskToken)) {
						$this->log(
							'info',
							'PollForDecisionTask decision task received'
						);

						// set relevant amazon ids
						$this->setAmazonRunId((string)$response->body->workflowExecution->runId);
						$this->setAmazonWorkflowId((string)$response->body->workflowExecution->workflowId);

						try {
							$decision = $this->decide(
								new HistoryEventIterator($this->getAmazonClass(), $pollRequest, $response)
							);
						} catch (\Exception $e) {
							$this->log(
								'critical',
								'Exception while making decision: '.get_class($e).' - '.$e->getMessage(),
								array(
									'decision' => $this->createSWFDecisionArray($decision),
									'trace' => $e->getTrace()
								)
							);

							// continue to the next "poller" loop
							continue;
						}

						$decisionArray = array(
							'taskToken' => $taskToken,
							'decisions' => $this->createSWFDecisionArray($decision)
						);

						$completeResponse = $this->amazonClass->respond_decision_task_completed($decisionArray);

						if ($completeResponse->isOK()) {
							$this->log(
								'info',
								'Decision made (RespondDecisionTaskCompleted successful)',
								array(
									'decisionArray' => $decisionArray
								)
							);
						} else {
							$this->log(
								'critical',
								'Decision failed (RespondDecisionTaskCompleted failed)',
								array(
									'decisionArray' => $decisionArray,
									'response' => $completeResponse
								)
							);
						}
					// received empty response
					} else {
						$this->log(
							'debug',
							'PollForDecisionTask received empty response'
						);
					}
				} else {
					$this->log(
						'critical',
						'PollForDecisionTask failed',
						array(
							'response' => $response->body
						)
					);
				}
			}
		} catch (\Exception $e) {
			$this->log(
				'critical',
				'Exception in decider worker: '.get_class($e).' - '.$e->getMessage(),
				array(
					'trace' => $e->getTrace()
				)
			);
		}
	}

	/**
	 * Decider logic.
	 *
	 * Creates a decision object that is passed to each event in history (via self::processEvent()).
	 * Each event class has the opportunity to modify the Decision object by adding
	 * decision events to it.
	 *
	 * @access private
	 * @final
	 * @param HistoryEventIterator $history
	 * @return Decision
	 * @uses processEvent
	 */
	final private function decide(HistoryEventIterator $history)
	{
		$maxEventId = 0;

		// we have a decision object who will be passed to each event in history
		// if they have a corresponding class. Each event class can change the state
		// of the decision by adding, removing or editiing decision events.
		$decision = new Decision;

		foreach ($history as $event) {
			try {
				$this->processEvent($decision, $event, $maxEventId);
			} catch (\Exception $e) {
				// log the actual event that failed
				$this->log(
					'critical',
					'Exception while processing event: '.get_class($e).' - '.$e->getMessage(),
					array(
						'event' => $event,
						'trace' => $e->getTrace()
					)
				);

				// let the decider know there was a problem
				throw new \Exception('Failed processing event');
			}
		}

		return $decision;
	}

	/**
	 * Process a given history event.
	 *
	 * This will look for a class having a name matching that of the passed $event.
	 * If it finds one (and the class extends AbstractHistoryEvent), the class will
	 * be passed our Decision object. That event class has the opportunity to change
	 * the Decision object.
	 *
	 * @access private
	 * @final
	 * @param array $event
	 * @param Decision $decision
	 * @param int $maxEventId
	 */
	final private function processEvent(Decision $decision, $event, &$maxEventId)
	{
		$maxEventId = max($maxEventId, intval($event->eventId));

		$eventType = (string)$event->eventType;
		$eventId = (int)$event->eventId;

		// save the events for later lookups
		$this->events[$eventId] = array(
			'event_type' => $eventType,
			'activity_type' => ($eventType == 'ActivityTaskScheduled' ? (string)$event->activityTaskScheduledEventAttributes->activityType->name : null)
		);

		$defaultEventNamespace = 'Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\HistoryEvent';

		$userClass = $this->eventNamespace.'\\'.$eventType;
		$defaultClass = $defaultEventNamespace.'\\'.$eventType;

		if (class_exists($userClass)) {
			$this->log(
				'debug',
				"Processing decision event [$eventId - $eventType] - user class found",
				array(
					'user class' => $userClass,
					'default class' => $defaultClass,
					'event' => json_encode($event)
				)
			);

			$obj = new $userClass;

			if (!($obj instanceof AbstractHistoryEvent)) {
				throw new InvalidClassException($userClass.' must extend AbstractHistoryEvent'); 
			}

			$obj->run($this, $decision, $event, $maxEventId);
		} elseif (class_exists($defaultClass)) {
			$this->log(
				'debug',
				"Processing decision event [$eventId - $eventType] - default class found",
				array(
					'user class' => $userClass,
					'default class' => $defaultClass,
					'event' => json_encode($event)
				)
			);

			$obj = new $defaultClass;

			if (!($obj instanceof AbstractHistoryEvent)) {
				throw new InvalidClassException($userClass.' must extend AbstractHistoryEvent'); 
			}

			$obj->run($this, $decision, $event, $maxEventId);
		} else {
			$this->log(
				'debug',
				"Processing decision event [$eventId - $eventType] - no class",
				array(
					'user class' => $userClass,
					'default class' => $defaultClass,
					'event' => json_encode($event)
				)
			);
		}
	}

	/**
	 * Given a decision object, create a decision array appropriate for amazon's SDK.
	 *
	 * @access public
	 * @param Decision $decision
	 * @return array
	 */
	public static function createSWFDecisionArray(Decision $decision)
	{
		$ret = array();
		foreach ($decision->getDecisionEvents() as $e)
		{
			$title = $e->getTitle();
			$ret[] = array(
				'decisionType' => $title,
				lcfirst($title).'DecisionAttributes' => json_decode(json_encode($e), true)
			);
		}
		return $ret;
	}

	/**
	 * Registers the workflow.
	 *
	 * @access public
	 * @final
	 * @return mixed
	 * @throws InvalidConfigurationException
	 *
	 * @todo registration should be decoupled methods of the code that this code calls.
	 */
	final public function registerWorkflow()
	{
		// TODO allow for all options at http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html
		$registerRequest = array(
			'domain' => $this->domain,
			'name' => $this->name,
			'version' => (string)$this->workflowVersion
		);

		if ($this->defaultChildPolicy) {
			$registerRequest['defaultChildPolicy'] = $this->defaultChildPolicy;
		}

		if ($this->defaultTaskList) {
			$registerRequest['defaultTaskList'] = array('name' => $this->defaultTaskList);
		}

		if ($this->defaultTaskStartToCloseTimeout) {
			$registerRequest['defaultTaskStartToCloseTimeout'] = (string)$this->defaultTaskStartToCloseTimeout;
		}

		if ($this->defaultExecutionStartToCloseTimeout) {
			$registerRequest['defaultExecutionStartToCloseTimeout'] = (string)$this->defaultExecutionStartToCloseTimeout;
		}


		$response = $this->amazonClass->register_workflow_type($registerRequest);

		$this->log(
			'info',
			'Registering workflow',
			array(
				'request' => $registerRequest,
				'response' => json_decode(json_encode($response), true)
			)
		);

		if (!$response->isOK() && $response->body->__type != 'com.amazonaws.swf.base.model#TypeAlreadyExistsFault') {
			$this->log(
				'alert',
				'Could not register workflow'
			);

			exit;
		}

		return $this->amazonClass->describe_workflow_type($registerRequest);
	}

	/**
	 * Registers activities in this workflow
	 *
	 * @access protected
	 * @todo TODO check for existing activities and don't make the call unless that activity/version/domain combo is not yet registered.
	 *
	 * @todo registration should be decoupled methods of the code that this code calls.
	 */
	protected function registerActivities()
	{
		$arr = $this->getActivityArray();
		$domain = $this->amazonClass->getConfig()->get('domain');

		$this->log(
			'info',
			'Registering activities in '.$arr['directory'],
			array(
				'activities' => $arr
			)
		);

		foreach (glob($arr['directory'].'/*.php') as $file) {
			$base = substr(basename($file), 0, -4);
			$class = $arr['namespace'].'\\'.$base;

			$this->log(
				'debug',
				'Attempting to register activity \''.$base.'\''
			);

			if (!class_exists($class)) {
				// don't error here. user may have legitimate file int his
				// dir that just isn't an activity class.
				$this->log(
					'warning',
					'Found activity file '.$file.' but it does not have the expected class '.$class.' in it. Skipping.'
				);

				continue;
			}

			$obj = new $class;

			if (!($obj instanceof AbstractActivity)) {
				// don't error here. user may have legitimate file in his
				// dir that just isn't an activity class.
				$this->log(
					'warning',
					'Found activity file '.$file.' but it is not an instance of AbstractActivity. Skipping.'
				);

				continue;
			}

			$request = array(
				'domain' => $domain,
				'name' => $base,
				'version' => $this->activityVersion,
			);

			if ($arr['default_task_list']) {
				$request['defaultTaskList'] = array('name' => $arr['default_task_list']);
			}

			// register type (ignoring "already exists" fault for now)
			$response = $this->amazonClass->register_activity_type($request);

			$this->log(
				'debug',
				'RegisterActivityType',
				array(
					'request' => $request,
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
	 * Get the namespace where activities are located
	 *
	 * @access public
	 * @return string
	 */
	public function getEventActivityNamespace()
	{
		return $this->activityNamespace;
	}

	/**
	 * Get our event record
	 *
	 * @access public
	 * @return array self::$events
	 */
	public function getEvents()
	{
		return $this->events;
	}
}
