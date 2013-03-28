<?php
/**
 * @author Aaron Scherer, John Pancoast
 * @date   2/20/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decision;

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
	 * Event logic
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
	 * @param DeciderWorker $decider
	 * @param Decision $decision
	 * @param array $event
	 * @param int $maxEventId
	 * @return void
	 */
	public function run(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
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