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
	 * Constructor
	 */
	abstract public function __construct();

	/**
	 * Logic for the event
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
		$function = $this->eventLogic;
		return $function( $event, $workflowState, $timerOptions, $activityOptions, $continueAsNew, $maxEventId );
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

	/**
	 * @param callable $eventLogic
	 */
	public function setEventLogic( \Closure $eventLogic )
	{
		$this->eventLogic = $eventLogic;
	}

	/**
	 * @return callable
	 */
	public function getEventLogic( )
	{
		return $this->eventLogic;
	}


}
