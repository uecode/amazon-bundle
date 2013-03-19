<?php
/**
 * @author Aaron Scherer
 * @date   2/20/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\Decider;

// Amazon Bundle's SWF Components
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\AbstractEvent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\State\DeciderWorkerState;

class ActivityTaskCompletedEvent extends AbstractEvent
{

	public function __construct()
	{
		$this->setEventType( 'ActivityTaskCompleted' );

		$this->setEventLogic( function( $event, &$workflowState, &$timerOptions, &$activityOptions, &$continueAsNew, &$maxEventId ) {
			if ( $workflowState === DeciderWorkerState::ACTIVITY_OPEN ) {
				$workflowState = DeciderWorkerState::NOTHING_OPEN;
			} else if ( $workflowState === DeciderWorkerState::TIMER_AND_ACTIVITY_OPEN ) {
				$workflowState = DeciderWorkerState::TIMER_OPEN;
			}
		} );
	}
}
