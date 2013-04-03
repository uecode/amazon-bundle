<?php
/**
 * Activity event interface for activities that occur in history events.
 *
 * @author John Pancoast
 * @date 2013-04-02
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_HistoryEvent.html
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

interface HistoryActivityEventInterface
{
	/**
	 * Logic that gets run when the activity had an ActivityTaskStarted
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventStartedLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * Logic that gets run when the activity had an ActivityTaskCompleted
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventCompletedLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * Logic that gets run when the activity had an ActivityTaskFailed
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventFailedLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * Logic that gets run when the activity had an ActivityTaskTimedOut
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventTimedOutLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);

	/**
	 * Logic that gets run when the activity had an ActivityTaskCanceled
	 *
	 * The goal of your event's implementation is to modify the Decision object.
	 * Each event gradually changes the state of the decision.
	 *
	 * @access public
	 * @param DeciderWorker $decider The decider object
	 * @param Decision $decision The decision object 
	 * @param array $event The event in history we're dealing with.
	 * @param int $maxEventId The max event id.
	 */
	public function activityEventCanceledLogic(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId);
}