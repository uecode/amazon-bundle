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

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow;

// Amazon Components
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\HistoryEventIterator;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\State\DeciderWorkerState;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\Worker;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Events
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\AbstractHistoryEvent;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;
use \CFResponse;

class DeciderWorker extends Worker
{
	/**
	 * @var string Workflow name used for registration
	 *
	 * @access private
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RegisterWorkflowType.html#SWF-RegisterWorkflowType-request-name
	 */
	private $name;

	/**
	 * @var string Task list to poll on
	 *
	 * @access private
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html#SWF-PollForDecisionTask-request-taskList
	 */
	private $taskList;

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
	 * @param SimpleWorkflow $swf
	 * @param array $workflowType
	 * @param string $name Workflow name to poll on
	 * @param string $taskList Task list to poll on
	 */
	final public function __construct(SimpleWorkflow $swf, $domain, $name, $taskList) {
		parent::__construct($swf);

		$this->domain = $domain;
		$this->name = $name;
		$this->taskList = $taskList;
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
				$this->response = null;

				// these values can only be set from amazon response
				$this->setAmazonRunId(null);
				$this->setAmazonWorkflowId(null);

				// poll amazon for decision task and handle if successful
				// http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForDecisionTask.html
				$pollRequest = array(
					'domain' => $this->domain,
					'taskList' => array('name' => $this->taskList)
				);

				$this->response = $this->getSWFObject()->callSDK('PollForDecisionTask', $pollRequest);

				$this->log(
					'debug',
					'PollForDecisionTask',
					array(
						'request' => $pollRequest,
						'response' => json_decode(json_encode($this->response), true)
					)
				);

				if ($this->response->isOK()) {
					// unique id for this task which is used when we make our RespondDecisionTaskCompleted call.
					$taskToken = (string)$this->response->body->taskToken;

					if (!empty($taskToken)) {
						$this->log(
							'info',
							'PollForDecisionTask decision task received'
						);

						// set relevant amazon ids
						$this->setAmazonRunId((string)$this->response->body->workflowExecution->runId);
						$this->setAmazonWorkflowId((string)$this->response->body->workflowExecution->workflowId);

						try {
							// start main decision logic
							$decision = $this->decide(new HistoryEventIterator($this->getAmazonObject(), $pollRequest, $this->response));
						} catch (\Exception $e) {
							$this->log(
								'critical',
								'Exception while making decision: '.get_class($e).' - '.$e->getMessage(),
								array(
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

						$completeResponse = $this->getSWFObject()->callSDK('RespondDecisionTaskCompleted', $decisionArray);

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
							'response' => $this->response->body
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
		$maxEventId = 01;

		// we have a decision object who will be passed to each event in history
		// if they have a corresponding class. Each event class can change the state
		// of the decision by adding, removing or editiing decision events.
		$decision = new Decision;

		try {
			foreach ($history as $event) {
				$this->processEvent($decision, $event, $maxEventId);
			}
		} catch (\Exception $e) {
			// log the actual event that failed
			$this->log(
				'critical',
				'Exception while processing event',
				array(
					'event' => json_decode(json_encode($event), true),
					'saved_events' => $this->events,
					'exception' => get_class($e).' - '.$e->getMessage(),
					'trace' => $e->getTrace()
				)
			);

			// let the decider know there was a problem
			throw new \Exception('Exception while processing event: '.get_class($e).' - '.$e->getMessage());
		}

		return $decision;
	}

	/**
	 * Process a given history event.
	 *
	 * This will pass the decision object to a decider to process to this particular event.
	 * There are 2 kinds of deciders this decision object can be passed to, a Decider object or an
	 * Activity Task. Which one is chosen is based on the type of this history event. If it 
	 * is of a type related to a specific activity, it will use a specific ActivityTask you've
	 * configured in your activities section in the config, otherwise this will just use the decider
	 * you configured in your workflow.
	 *
	 * @access private
	 * @final
	 * @param array $event
	 * @param Decision $decision
	 * @param int $maxEventId
	 */
	final private function processEvent(Decision &$decision, $event, &$maxEventId)
	{
		$maxEventId = max($maxEventId, intval($event->eventId));

		// this history event basic info
		$eventType = (string)$event->eventType;
		$eventId = (int)$event->eventId;

		// save the events for later lookups.
		$this->events[$eventId] = array(
			'event_type' => $eventType,
			'activity_type' => ($eventType == 'ActivityTaskScheduled' ? (string)$event->activityTaskScheduledEventAttributes->activityType->name : null),
			'activity_type_version' => ($eventType == 'ActivityTaskScheduled' ? (string)$event->activityTaskScheduledEventAttributes->activityType->version : null)
		);

		// get the name and version of the workflow type
		$workflowName = $this->response->body->workflowType->name;
		$workflowVersion = $this->response->body->workflowType->version;

		// now we find a certain decider object based on this event type.
		// If this eventType is of a type matching *activityTask* then we use
		// an ActivityTask to handle this event's part in the decision.
		switch ($eventType) {
			// activity related types
			case 'ActivityTaskScheduled':
			case 'ScheduleActivityTaskFailed':
			case 'ActivityTaskStarted':
			case 'ActivityTaskCompleted':
			case 'ActivityTaskFailed':
			case 'ActivityTaskTimedout':
			case 'ActivityTaskCanceled':
			case 'ActivityTaskCancelRequested':
			case 'RequestCancelActivityTaskFailed':
				// grab the activity task name that this activity event is referencing
				$attrKey = lcfirst($eventType).'EventAttributes';
				$events = $this->getEvents();
				$scheduledId = $eventType == 'ActivityTaskScheduled' ? $eventId : (int)$event->{$attrKey}->scheduledEventId;
				$activityType = $events[$scheduledId]['activity_type'];
				$activityVersion = $events[$scheduledId]['activity_type_version'];

				if (!$activityType || !$activityVersion) {
					throw new \Exception("Couldn't find referenced activity type in saved events");
				}

				$class = $this->getActivityClass($activityType, $activityVersion);

				if (!class_exists($class)) {
					throw new InvalidClassException('Cannot find activity class to process this event.');
				}

				$obj = new $class;

				if (!($obj instanceof DeciderActivityTaskInterface)) {
					throw new InvalidClassException("Activity class '$class' must implement 'DeciderActivityTaskInterface'.");
				}

				$this->log(
					'debug',
					"Processing decision event with activity task decider",
					array(
						'class' => $class,
						'event' => json_decode(json_encode($event, true))
					)
				);

				break;
			// all other event types use your normal decider
			default:
				$class = $this->getDeciderClass($workflowName, $workflowVersion);

				if (!class_exists($class)) {
					throw new InvalidClassException('Cannot find activity class to process this event.');
				}

				$obj = new $class;

				if (!($obj instanceof DeciderInterface)) {
					throw new InvalidClassException("Decider class '$class' must implement 'DeciderInterface'.");
				}

				$this->log(
					'debug',
					"Processing decision event with normal decider",
					array(
						'class' => $class,
						'event' => json_decode(json_encode($event, true))
					)
				);

				break;
		}

		// if decider object has properly impemented appropriate interface, it will have
		// this method defined.
		$method = lcfirst($eventType);

		// let this event play it's part in the decision making.
		$obj->{$method}($this, $decision, $event, $maxEventId);
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
