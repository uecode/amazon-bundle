<?php
/**
 * @author Aaron Scherer, John Pancoast
 * @date   2/20/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\Decider;

// Amazon Bundle's SWF Components
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\AbstractEvent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\State\DeciderWorkerState;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decider;


class WorkflowExecutionStartedEvent extends AbstractEvent
{
	protected $eventType = 'WorkflowExecutionStarted';

	// This is the only case which doesn't only transition state;
	// it also gathers the user's workflow input.
	protected function eventLogic( $event, &$workflowState, &$timerOptions, &$activityOptions, &$continueAsNew, &$maxEventId ) {
		$workflowState = DeciderWorkerState::START;

		// gather gather gather
		$eventAttributes = $event->workflowExecutionStartedEventAttributes;
		$workflowInput = json_decode( $eventAttributes->input, true );

		$activityOptions = Decider::createActivityOptions( $workflowInput );
		$timerOptions = Decider::createActivityOptions( $workflowInput );
		$continueAsNew = Decider::createContinueOptions( $eventAttributes );
	}
}
