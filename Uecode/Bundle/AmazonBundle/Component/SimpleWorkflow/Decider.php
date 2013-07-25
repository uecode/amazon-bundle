<?php

/**
 * For handling decision events
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

use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DeciderInterface;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\Decision;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DecisionEvent\ScheduleActivityTask;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\Util;

/**
 * For handling decision events
 *
 * Your workflow decider must extend this. When the {@see DeciderWorker} loops event history,
 * it will pass each event through the matching method here. It will be passed a {@see Decision} object.
 * Your decider can add handlers for those events and add {@see DecisionEvent}'s to the 
 * Decision object.
 *
 * @see DeciderInterface
 * @author John Pancoast
 * @see DeciderInterface
 */
class Decider implements DeciderInterface
{
	/**
	 * @see DeciderInterface
	 */
	public function workflowExecutionStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function workflowExecutionCompleted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function workflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function workflowExecutionTimedOut(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function workflowExecutionCanceled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function workflowExecutionTerminated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function workflowExecutionContinuedAsNew(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function workflowExecutionCancelRequested(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function decisionTaskScheduled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function decisionTaskStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function decisionTaskCompleted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function decisionTaskTimedOut(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function requestCancelActivityTaskFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function workflowExecutionSignaled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function markerRecorded(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function timerStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function startTimerFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function timerFired(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function timerCanceled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function cancelTimerFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function startChildWorkflowExecutionInitiated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function startChildWorkflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function childWorkflowExecutionStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function childWorkflowExecutionCompleted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function childWorkflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function childWorkflowExecutionTimedOut(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function childWorkflowExecutionCanceled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function childWorkflowExecutionTerminated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function signalExternalWorkflowExecutionInitiated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function externalWorkflowExecutionSignaled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function signalExternalWorkflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function requestCancelExternalWorkflowExecutionInitiated(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function externalWorkflowExecutionCancelRequested(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderInterface
	 */
	public function requestCancelExternalWorkflowExecutionFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}
}
