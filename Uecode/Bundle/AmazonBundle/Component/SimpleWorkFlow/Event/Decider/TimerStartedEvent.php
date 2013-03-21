<?php
/**
 * @author Aaron Scherer, John Pancoast
 * @date   2/20/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\Decider;

// Amazon Bundle's SWF Components
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\AbstractEvent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\State\DeciderWorkerState;

class TimerStartedEvent extends AbstractEvent
{
	$this->eventType = 'TimerStarted';

	protected function eventLogic( $event, &$workflowState, &$timerOptions, &$activityOptions, &$continueAsNew, &$maxEventId ) {
		if ( $workflowState === DeciderWorkerState::NOTHING_OPEN || $workflowState === DeciderWorkerState::START ) {
			$workflowState = DeciderWorkerState::TIMER_OPEN;
		} else {
			if ( $workflowState === DeciderWorkerState::ACTIVITY_OPEN ) {
				$workflowState = DeciderWorkerState::TIMER_AND_ACTIVITY_OPEN;
			}
		}
	}
}
