<?php
/**
 * @author Aaron Scherer
 * @date   2/20/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\Type\Decider;

// Amazon Bundle's SWF Components
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\AbstractEvent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\State\DeciderWorkerState;

class ActivityTaskScheduledEvent extends AbstractEvent
{

	public function __construct()
	{
		$this->setEventType( 'ActivityTaskScheduled' );
	}

	public function run( $event, &$workflowState, &$timerOptions, &$activityOptions, &$continueAsNew, &$maxEventId )
	{
		if ( $workflowState === DeciderWorkerState::NOTHING_OPEN ) {
			$workflowState = DeciderWorkerState::ACTIVITY_OPEN;
		} else if ( $workflowState === DeciderWorkerState::TIMER_OPEN ) {
			$workflowState = DeciderWorkerState::TIMER_AND_ACTIVITY_OPEN;
		}
	}
}
