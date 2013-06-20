<?php

/**
 * Abstract activity type
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
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

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

// amazon bundle
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;
use Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\ActivityWorker;

// amazon
use \CFResponse;

abstract class AbstractActivity
{
	/**
	 * Activity logic that gets executed when an activity worker assigns work
	 *
	 * Note that the activity is considered successful unless you explicitly return false.
	 * The string you return from the method is sent as the "result" field in the 
	 * RespondActivityTaskCompleted request.
	 *
	 * @abstract
	 * @access protected
	 * @param string $taskToken The unique token id that amazon provided us for this job.
	 * @param AbstractActivity $activity
	 * @param CFResponse $response The response received from polling amazon for an activity
	 * @return ActivityTaskResponse
	 */
	abstract protected function activityLogic($taskToken, ActivityWorker $activity, CFResponse $response);

	/**
	 * Run activity logic
	 *
	 * @access public
	 * @param string $taskToken The unique token id that amazon provided us for this job.
	 * @param AbstractActivity $activity
	 * @param CFResponse $response The response received from polling amazon for an activity
	 * @return ActivityTaskResponse
	 */
	public function run($taskToken, ActivityWorker $activity, CFResponse $response)
	{
		$resp = $this->activityLogic($taskToken, $activity, $response);

		if (!($resp instanceof ActivityTaskResponse)) {
			throw new InvalidClassException('Activity::activityLogic() must return ActivityTaskResponse ['.get_class($this).']');
		}

		return $resp;
	}
}
