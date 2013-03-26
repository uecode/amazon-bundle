<?php
/**
 * @author Aaron Scherer, John Pancoast
 * @date   2/20/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decider;

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
	 * @param Decider $decider
	 * @param Decision $decision
	 * @return void
	 */
	abstract protected function eventLogic(Decider $decider, Decision $decision)

	/**
	 * Run logic for the event. At the moment this serves as an abstraction between client and self::eventLogic().
	 *
	 * @param Decider $decider
	 * @param Decision $decision
	 * @return mixed
	 */
	public function run(Decider $decider, Decision $decision)
	{
		$this->eventLogic($decider, $decision);
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