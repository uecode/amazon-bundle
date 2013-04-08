<?php

/**
 * For handling any of the ActivityTask* events
 *
 * All of the ActivityTask* classes should extend this one.
 *
 * @package amazon-bundle
 * @copyright Underground Elephant
 * @author John Pancoast
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\HistoryEvent;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Amazon component
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\AbstractHistoryEvent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decision;
use Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DecisionEvent\ScheduleActivityTask;

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
	/**
	 * Handle event logic for an activity task event in history.
	 *
	 * This works by handling all of the ActivityTask* events in history, grabbing
	 * the activity task name that they are referring to, then calling the 
	 * activity event class' activityType* method.
	 *
	 * @see AbstractHistoryEvent::eventLogic()
	 */
	protected function eventLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
		// this event title
		$eventType = $this->getEventType();

		// grab the activity task that this activity event is referencing
		$attrKey = lcfirst($eventType).'EventAttributes';
		$events = $decider->getEvents();
		$scheduledId = (int)$event->{$attrKey}->scheduledEventId;
		$eventName = $events[$scheduledId]['activity_type'];

		if ($eventName) {
			$class = $decider->getActivityNamespace().'\\'.$eventName;
			$method = str_replace('ActivityTask', 'activityEvent', $eventType).'Logic';

			$obj = new $class;

			if (!($obj instanceof HistoryActivityEventInterface)) {
				throw new InvalidClassException('Activity history event must implement "HistoryActivityEventInterface"');
			}

			$obj->{$method}($decider, $decision, $event, $maxEventId);
		}
	}
}
