<?php

/**
 * For handling any of the ActivityTask* events
 *
 * All of the ActivityTask* classes should extend this one.
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

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\HistoryEvent;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Amazon component
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\AbstractHistoryEvent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\HistoryActivityEventInterface;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\Decision;
use Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DecisionEvent\ScheduleActivityTask;

/**
 * For handling any of the ActivityTask* events
 *
 * All of the ActivityTask* classes should extend this one.
 *
 * @package amazon-bundle
 * @copyright Underground Elephant
 * @author John Pancoast
 */
class ActivityTask extends AbstractHistoryEvent
{
	/*
	 * @inherit
	 *
	 * Handle event logic for an activity task event in history.
	 *
	 * This works by handling all of the ActivityTask* events in history, grabbing
	 * the activity task name that they are referring to, then calling the 
	 * activity event class' activityType* method.
	 *
	 */
	protected function event(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
		// this event title
		$eventType = $this->getEventType();

		// grab the activity task that this activity event is referencing
		$attrKey = lcfirst($eventType).'EventAttributes';
		$events = $decider->getEvents();
		$scheduledId = (int)$event->{$attrKey}->scheduledEventId;
		$eventName = $events[$scheduledId]['activity_type'];

		if ($eventName) {
			$name = $decider->getResponse()->body->workflowType->name;
			$version = $decider->getResponse()->body->workflowType->version;

			$class = $decider->getActivityEventNamespace($name, $version).'\\'.$eventName;
			$method = str_replace('ActivityTask', 'activityEvent', $eventType);

			$obj = new $class;

			if (!($obj instanceof HistoryActivityEventInterface)) {
				throw new InvalidClassException($class.' must implement "HistoryActivityEventInterface"');
			}

			$obj->{$method}($decider, $decision, $event, $maxEventId);
		}
	}
}
