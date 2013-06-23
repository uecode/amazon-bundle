<?php

/**
 * Activity event interface for activities that occur in history events.
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author John Pancoast
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html
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

interface HistoryActivityEventInterface
{
	/**
	 * Logic that gets run when the activity had an ActivityTaskStarted
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventStartedLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * Logic that gets run when the activity had an ActivityTaskCompleted
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventCompletedLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * Logic that gets run when the activity had an ActivityTaskFailed
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventFailedLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * Logic that gets run when the activity had an ActivityTaskTimedOut
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventTimedOutLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * Logic that gets run when the activity had an ActivityTaskCanceled
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventCanceledLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);
}
