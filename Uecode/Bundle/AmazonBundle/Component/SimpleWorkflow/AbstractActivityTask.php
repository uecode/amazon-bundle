<?php
/**
 * Abstraction of activity tasks.
 *
 * @package amazon-bundle
 * @copyright Underground Elephant
 * @author John Pancoast
 *
 * Copyright 2013 Underground Elephant
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow;

use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\ActivityTaskInterface;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DeciderActivityTaskInterface;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\ActivityWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\Decision;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DecisionEvent\ScheduleActivityTask;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DecisionEvent\CompleteWorkflowExecution;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\ActivityTaskResponse\ActivityTaskCompleted;

/**
 * Abstraction of activity tasks
 *
 * Each of your activity task must extend this.
 *
 * @abstract
 *
 * @author John Pancoast
 */
abstract class AbstractActivityTask implements DeciderActivityTaskInterface
{
	/**
	 * Activity logic that gets executed when an activity worker receives an activity task for this specific task.
	 *
	 * @abstract
	 * @access public
	 * @param AbstractActivity $activity
	 * @param string $taskToken The unique token id that amazon provided us for this job.
	 * @return ActivityTaskResponse
	 */
	abstract public function activity(ActivityWorker $activity, $taskToken);

	/**
	 * @see DeciderActivityTaskInterface::activityTaskScheduled()
	 */
	public function activityTaskScheduled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderActivityTaskInterface::scheduleActivityTaskFailed()
	 */
	public function scheduleActivityTaskFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderActivityTaskInterface::activityTaskStarted()
	 */
	public function activityTaskStarted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderActivityTaskInterface::activityTaskCompleted()
	 */
	public function activityTaskCompleted(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderActivityTaskInterface::activityTaskFailed()
	 */
	public function activityTaskFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderActivityTaskInterface::activityTaskTimedout()
	 */
	public function activityTaskTimedout(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderActivityTaskInterface::activityTaskCanceled()
	 */
	public function activityTaskCanceled(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderActivityTaskInterface::activityTaskCancelRequested()
	 */
	public function activityTaskCancelRequested(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}

	/**
	 * @see DeciderActivityTaskInterface::requestCancelActivityTaskFailed()
	 */
	public function requestCancelActivityTaskFailed(DeciderWorker $decider, Decision &$decision, $event, &$maxEventId)
	{
	}
}
