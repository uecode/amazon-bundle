<?php
/**
 * @author Aaron Scherer
 * @date   2013
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\State;

/*
 * A decider can be written by modeling the workflow as a state machine.
 * For complex workflows, this is the easiest model to use.
 *
 * The decider reads the history to figure out which state the workflow is currently in,
 * and makes a decision based on the current state.
 *
 * This implementation of the decider ignores activity failures.
 * You can handle them by adding more states.
 * This decider also only supports having a single activity open at a time.
 */
abstract class DeciderWorkerState
{
	/**
	 * A new workflow is in this state
	 */
	const START = 0;

	/**
	 * If a timer is open, and not an activity.
	 */
	const TIMER_OPEN = 1;

	/**
	 * If an activity is open, and not a timer.
	 */
	const ACTIVITY_OPEN = 2;

	/**
	 * If both a timer and an activity are open.
	 */
	const TIMER_AND_ACTIVITY_OPEN = 3;

	/**
	 * Nothing is open.
	 */
	const NOTHING_OPEN = 4;
}
