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
 * Interface for handling activity task related decision events
 *
 * Note that -only- *ActivityTask* related history events are included at
 * {@see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html}.
 *
 * Other history events are handled in {@see DeciderInterface}.
 *
 * @author John Pancoast
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html
 */
interface DeciderActivityTaskInterface
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
	public function activityTaskScheduled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

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
	public function scheduleActivityTaskFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

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
	public function activityTaskStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

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
	public function activityTaskCompleted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

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
	public function activityTaskFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

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
	public function activityTaskTimedOut(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

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
	public function activityTaskCanceled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

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
	public function activityTaskCancelRequested(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

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
	public function requestCancelActivityTaskFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);
}
