<?php

/**
 * Abstraction to handle history events.
 *
 * @package amazon-bundle
 * @copyright Underground Elephant
 * @author John Pancoast
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\HistoryEvent;

use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decision;

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
	 * @var callable
	 */
	protected $eventLogic;

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
	abstract protected function eventLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

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
		$this->eventLogic($decider, $decision, $event, $maxEventId);
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
