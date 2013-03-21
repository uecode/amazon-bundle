<?php
/**
 * @author Aaron Scherer
 * @date   2/20/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event;

abstract class AbstractEvent
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
	 * @param $event
	 * @param $workflowState
	 * @param $timerOptions
	 * @param $activityOptions
	 * @param $continueAsNew
	 * @param $maxEventId
	 * @return void
	 */
	abstract protected function eventLogic( $event, &$workflowState, &$timerOptions, &$activityOptions, &$continueAsNew, &$maxEventId );

	/**
	 * Run logic for the event
	 *
	 * @param $event
	 * @param $workflowState
	 * @param $timerOptions
	 * @param $activityOptions
	 * @param $continueAsNew
	 * @param $maxEventId
	 * @return mixed
	 */
	public function run( $event, &$workflowState, &$timerOptions, &$activityOptions, &$continueAsNew, &$maxEventId )
	{
		$this->eventLogic();
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
	final public function setEventType( $eventType )
	{
		$this->eventType = $eventType;
	}
}