<?php

/**
 * Abstraction to handle history events.
 *
 * @package amazon-bundle
 * @copyright Underground Elephant
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

use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\Decision;

/**
 * Interface for handling decision events
 *
 * Note this will include -most- history events located at
 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
 *
 * *ActivityTask* related history events are not included. These are instead
 * handled in {@see DeciderActivityTaskInterface}.
 *
 * @author John Pancoast
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html
 */
interface DeciderInterface
{
	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function workflowExecutionStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function workflowExecutionCompleted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function workflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function workflowExecutionTimedOut(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function workflowExecutionCanceled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function workflowExecutionTerminated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function workflowExecutionContinuedAsNew(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function workflowExecutionCancelRequested(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function decisionTaskScheduled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function decisionTaskStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function decisionTaskCompleted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function decisionTaskTimedOut(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function workflowExecutionSignaled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function markerRecorded(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function timerStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function startTimerFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function timerFired(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function timerCanceled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function cancelTimerFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function startChildWorkflowExecutionInitiated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function startChildWorkflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function childWorkflowExecutionStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function childWorkflowExecutionCompleted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function childWorkflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function childWorkflowExecutionTimedOut(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function childWorkflowExecutionCanceled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function childWorkflowExecutionTerminated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function signalExternalWorkflowExecutionInitiated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function externalWorkflowExecutionSignaled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);
	
	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function signalExternalWorkflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function requestCancelExternalWorkflowExecutionInitiated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function externalWorkflowExecutionCancelRequested(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * What the decider runs when this history event is encountered in history
	 *
	 * You can find this history event's docs at
	 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
	 *
	 * @access public
	 * @param DeciderWorker $decider Our decider object (for inversion of control if necessary).
	 * @param Decision $decision The decision object that this method should be modifying, adding, or removing events from.
	 * @param mixed $event The specific event data that this call will be working w/.
	 * @param int $maxEventId Incrementing ID
	 */
	public function requestCancelExternalWorkflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);
}
