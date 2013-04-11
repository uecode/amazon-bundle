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
	 * @var array
	 */
	private $workflowOptions = array();

	/** 
	 * @var string event namespace
	 */
	private $eventNamespace;

	/**
	 * @var string activity namespace
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
	 * @param \AmazonSWF $swf
	 * @param array $workflowType
	 * @param string $eventNamespace
	 * @param string $activityNamepsace
	 *
	 * @todo TODO change this to accept the workflowType array as individual values
	 * that the class will hold as class properties (cleaner).
	 * 
	 */
	final public function __construct(AmazonSWF $swf, array $workflowType, $eventNamespace, $activityNamespace)
	{
		parent::__construct($swf);

		$this->workflowOptions = $workflowType;
		$this->eventNamespace = $eventNamespace;
		$this->activityNamespace = $activityNamespace;

		$this->registerWorkflow();
		$this->registerActivities();
	}

	/**
	 * Run the workflow!
	 */
	final public function run()
	{
		$this->logger->log(
			'info',
			'Starting decider worker poll',
			SimpleWorkflow::logContext(
				'decider',
				$this->executionId
			)
		);

		try {
			while (true) {
				// these values can only be set from amazon response
				$this->amazonRunId = null;
				$this->amazonWorkflowId = null;

				// poll amazon for decision task and handle if successful
				$response = $this->amazonClass->poll_for_decision_task($this->workflowOptions);
				if ($response->isOK()) {
					// unique id for this task which is used when we make our RespondDecisionTaskCompleted call.
					$taskToken = (string)$response->body->taskToken;

					if (!empty($taskToken)) {
						$this->logger->log(
							'info',
							'PollForDecisionTask response received',
							SimpleWorkflow::logContext(
								'decider',
								$this->executionId,
								$this->amazonRunId,
								$this->amazonWorkflowId,
								$response->body
							)
						);

						// set relevant amazon ids
						$this->amazonRunId = (string)$response->body->workflowExecution->runId;
						$this->amazonWorkflowId = (string)$response->body->workflowExecution->workflowId;

						try {
							$decision = $this->decide(
								new HistoryEventIterator($this->getAmazonClass(), $this->workflowOptions, $response)
							);
						} catch (\Exception $e) {
							$this->logger->log(
								'critical',
								'Exception while making decision: '.get_class($e).' - '.$e->getMessage(),
								SimpleWorkflow::logContext(
									'decider',
									$this->executionId,
									$this->amazonRunId,
									$this->amazonWorkflowId,
									array(
										'decision' => $this->createSWFDecisionArray($decision),
										'trace' => $e->getTrace()
									)
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
							$this->logger->log(
								'info',
								'Decision made (RespondDecisionTaskCompleted successful)',
								SimpleWorkflow::logContext(
									'decider',
									$this->executionId,
									$this->amazonRunId,
									$this->amazonWorkflowId,
									$decisionArray
								)
							);
						} else {
							$this->logger->log(
								'critical',
								'Decision failed (RespondDecisionTaskCompleted failed)',
								SimpleWorkflow::logContext(
									'decider',
									$this->executionId,
									$this->amazonRunId,
									$this->amazonWorkflowId,
									array(
										'decisionArray' => $decisionArray,
										'response' => $completeResponse
									)
								)
							);
						}
					// received empty response
					} else {
						$this->logger->log(
							'info',
							'PollForDecisionTask received empty response',
							SimpleWorkflow::logContext(
								'decider',
								$this->executionId,
								$this->amazonRunId,
								$this->amazonWorkflowId
							)
						);
					}
				} else {
					$this->logger->log(
						'critical',
						'PollForDecisionTask failed',
						SimpleWorkflow::logContext(
							'decider',
							$this->executionId,
							$this->amazonRunId,
							$this->amazonWorkflowId,
							$response->body
						)
					);
				}
			}
		} catch (\Exception $e) {
			// make this a high level log alert due to the fact
			// that it shouldn't really happen.
			$this->logger->log(
				'alert',
				'Uncaught exception when attempting to make decision: '.get_class($e).' - '.$e->getMessage(),
				SimpleWorkflow::logContext(
					'decider',
					$this->executionId,
					$this->amazonRunId,
					$this->amazonWorkflowId,
					$e->getTrace()
				)
			);
		}
	}

	/**
	 * Decider logic. Runs through each history event and returns a decision.
	 *
	 * @param HistoryEventIterator $history
	 * @return Decision
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
				$this->logger->log(
					'critical',
					'Exception while processing event: '.get_class($e).' - '.$e->getMessage(),
					SimpleWorkflow::logContext(
						'decider',
						$this->executionId,
						$this->amazonRunId,
						$this->amazonWorkflowId,
						array(
							'event' => $event,
							'trace' => $e->getTrace()
						)
					)
				);

				// let the decider know there was a problem
				throw new \Exception('Failed processing event');
			}
		}

		return $decision;
	}

	/**
	 * Process the given history event
	 *
	 * @param array $event
	 * @param Decision $decision
	 * @param int $maxEventId
	 */
	protected function processEvent($decision, $event, &$maxEventId)
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
			$this->logger->log(
				'debug',
				"Processing decision event [$eventId - $eventType] - user class found",
				SimpleWorkflow::logContext(
					'decider',
					$this->executionId,
					$this->amazonRunId,
					$this->amazonWorkflowId,
					array(
						'user class' => $userClass,
						'default class' => $defaultClass,
						'event' => json_encode($event)
					)
				)
			);

			$obj = new $userClass;

			if (!($obj instanceof AbstractHistoryEvent)) {
				throw new InvalidClassException($userClass.' must extend AbstractHistoryEvent'); 
			}

			$obj->run($this, $decision, $event, $maxEventId);
		} elseif (class_exists($defaultClass)) {
			$this->logger->log(
				'debug',
				"Processing decision event [$eventId - $eventType] - default class found",
				SimpleWorkflow::logContext(
					'decider',
					$this->executionId,
					$this->amazonRunId,
					$this->amazonWorkflowId,
					array(
						'user class' => $userClass,
						'default class' => $defaultClass,
						'event' => json_encode($event)
					)
				)
			);

			$obj = new $defaultClass;

			if (!($obj instanceof AbstractHistoryEvent)) {
				throw new InvalidClassException($userClass.' must extend AbstractHistoryEvent'); 
			}

			$obj->run($this, $decision, $event, $maxEventId);
		} else {
			$this->logger->log(
				'debug',
				"Processing decision event [$eventId - $eventType] - no class",
				SimpleWorkflow::logContext(
					'decider',
					$this->executionId,
					$this->amazonRunId,
					$this->amazonWorkflowId,
					array(
						'user class' => $userClass,
						'default class' => $defaultClass,
						'event' => json_encode($event)
					)
				)
			);
		}
	}

	/**
	 * Given a decision object, create a decision array appropriate for amazon's SDK.
	 *
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
				lcfirst($title).'DecisionAttributes' => $e
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
	 */
	final public function registerWorkflow()
	{
		if ( !array_key_exists( 'name', $this->workflowOptions ) ) {
			throw new InvalidConfigurationException( "Name must be included in the second argument." );
		}

		if ( !array_key_exists( 'version', $this->workflowOptions ) ) {
			throw new InvalidConfigurationException( "Version must be included in the second argument." );
		}

		if ( !array_key_exists( 'domain', $this->workflowOptions ) ) {
			throw new InvalidConfigurationException( "Domain must be included in the third argument." );
		}

		$response = $this->amazonClass->register_workflow_type( $this->workflowOptions );
		if (!$response->isOK() && $response->body->__type != 'com.amazonaws.swf.base.model#TypeAlreadyExistsFault') {
			$this->logger->log(
				'alert',
				'Could not register decider worker',
				SimpleWorkflow::logContext(
					'decider',
					$this->executionId,
					$this->amazonRunId,
					$this->amazonWorkflowId,
					array(
						'response' => $response,
						'trace' => debug_backtrace()
					)
				)
			);

			exit;
		}

		return $this->amazonClass->describe_workflow_type( $this->workflowOptions );
	}

	/**
	 * Registers activities in this workflow
	 *
	 * @final
	 * @access protected
	 * @todo TODO check for existing activities and don't make the call unless that activity/version/domain combo is not yet registered.
	 */
	protected function registerActivities()
	{
		$av = $this->amazonClass->getActivityArray();
		$domain = $this->amazonClass->getConfig()->get('domain');
		foreach (glob($av['directory'].'/*.php') as $file)
		{
			$base = substr(basename($file), 0, -4);
			$class = $av['namespace'].'\\'.$base;
			$obj = new $class;
			if ($obj instanceof AbstractActivity) {
				$opts = array(
					'domain' => $domain,
					'name' => $base,
					'version' => $obj->getVersion(),
					'defaultTaskList' => array('name' => $av['default_task_list'])
				);

				// register type (ignoring "already exists" fault for now)
				$response = $this->amazonClass->register_activity_type($opts);
				if (!$response->isOK() && $response->body->__type != 'com.amazonaws.swf.base.model#TypeAlreadyExistsFault') {
					$this->logger->log(
						'alert',
						'Could not register activity',
						SimpleWorkflow::logContext(
							'decider',
							$this->executionId,
							$this->amazonRunId,
							$this->amazonWorkflowId,
							array(
								'response' => $response,
								'trace' => debug_backtrace()
							)
						)
					);

					exit;
				}
			}
		}
	}

	/**
	 * Get the namespace where activities are located
	 * @return [type]
	 */
	public function getActivityNamespace()
	{
		return $this->activityNamespace;
	}

	/**
	 * Get our event record
	 *
	 * @return array self::$events
	 */
	public function getEvents()
	{
		return $this->events;
	}
}
