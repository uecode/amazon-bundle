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
 * Abstraction to handle history events.
 *
 * @author John Pancoast
 * @date   3/26/13
 */
abstract class AbstractHistoryEvent
{
	/**
	 * Valid DeciderEvent
	 *
	 * @var string
	 */
	protected $eventType;

	/**
	 * Logic that gets run when this event occurs in history.
	 * 
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @param DeciderWorker $decider
	 * @param Decision $decision
	 * @param array $event
	 * @param int $maxEventId
	 * @return void
	 */
	abstract protected function event(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * Run logic for the event. At the moment this serves as an abstraction between client and self::eventLogic().
	 *
	 * @final
	 * @param DeciderWorker $decider
	 * @param Decision $decision
	 * @param array $event
	 * @param int $maxEventId
	 * @return void
	 */
	final public function run(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
		$this->event($decider, $decision, $event, $maxEventId);
	}

	/**
	 * @return string Returns a valid DeciderEvent
	 */
	final public function getEventType()
	{
		return $this->eventType;
	}

	/**
	 * Sets the Event Type. Must be a valid DeciderEvent
	 *
	 * @param string $eventType
	 */
	final public function setEventType($eventType)
	{
		$this->eventType = $eventType;
	}
}
