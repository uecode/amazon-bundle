<?php

/**
 * Activity worker
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

// Amazon Components
use \Uecode\Bundle\AmazonBundle\Model\SimpleWorkFlow;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Worker;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Amazon Classes
use \AmazonSWF;
use \CFResponse as CFResponse;

class ActivityWorker extends Worker
{
	/**
	 * @var string The task list this activity worker polls amazon for.
	 *
	 * @access protected
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForActivityTask.html
	 */
	protected $taskList;

	/**
	 * @var string A user-defined identity for this activity worker.
	 *
	 * @access protected
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForActivityTask.html
	 */
	protected $identity;

	/**
	 * constructor
	 *
	 * @access protected
	 * @param AmazonSWF $swf Simple workflow object
	 * @param string $taskList
	 * @param string $namespace
	 * @param string $identity
	 */
	public function __construct(AmazonSWF $swf, $taskList, $identity = null)
	{
		parent::__construct($swf);

		$this->taskList = $taskList;
		$this->identity = $identity;
	}

	/**
	 * Run the activity worker.
	 *
	 * This will make a request to amazon (a long poll) waiting for an activity task
	 * to perform. If amazon doesn't respond within a minute, they'll send an empty
	 * response and we'll start another loop. If they respond with an activity task to 
	 * we'll further process that {@see self::runActivity()}.
	 *
	 * @access public
	 */
	public function run()
	{
		$this->log(
			'info',
			'Starting activity worker poll'
		);

		while (true) {
			// these values can only be set from amazon response
			$this->setAmazonRunId(null);
			$this->setAmazonWorkflowId(null);

			$opts = array(
				'taskList' => array(
					'name' => $this->taskList,
				),
				'domain' => $this->amazonClass->getConfig()->get('domain'),
				'identity' => $this->identity
			);

			$response = $this->amazonClass->poll_for_activity_task($opts);
			if ($response->isOK()) {
				$taskToken = (string)$response->body->taskToken;

				if (!empty($taskToken)) {
					$this->log(
						'info',
						'PollForActivityTask response received',
						array(
							'taskToken' => $taskToken
						)
					);

					// set relevant amazon ids
					$this->setAmazonRunId((string)$response->body->workflowExecution->runId);
					$this->setAmazonWorkflowId((string)$response->body->workflowExecution->workflowId);

					$this->runActivity($response);
				} else {
					$this->log(
						'info',
						'PollForActivityTask received empty response'
					);
				}
			} else {
				$this->log(
					'critical',
					'PollForActivityTask failed'
				);
			}
		}
	}

	/**
	 * Given an activity worker response, run the activity.
	 *
	 * This will search for an activity class that matches the name in the response.
	 * It will search in the directory you specify in the uecode.amazon.simpleworkflow.domains.[domain].activities.directory
	 * config value. Activity classes must extend AbstractActivity.
	 *
	 * @access protected
	 */
	public function runActivity(CFResponse $response)
	{
		try {
			$name = $response->body->activityType->name;
			$token = (string)$response->body->taskToken;
			$activityArr = $this->amazonClass->getActivityArray();
			$class = $activityArr['namespace'].'\\'.$name;
			if (class_exists($class))
			{
				$this->log(
					'info',
					'Activity task class found',
					array(
						'class' => $class
					)
				);

				$obj = new $class;

				if (!($obj instanceof AbstractActivity)) {
					throw new InvalidClassException('Activity class "'.$class.'" must extend AbstractActivity.');
				}

				$taskResponse = $obj->run($token, $this, $response);
				$taskResponse->taskToken = $taskResponse->taskToken ?: $token;

				$method = 'respond_activity_task_'.str_replace('ActivityTask', '', basename(str_replace('\\', '/', get_class($taskResponse))));
				$completeResponse = $this->amazonClass->{$method}((array)$taskResponse);

				if ($completeResponse->isOK()) {
					$this->log(
						'info',
						'Activity completed (RespondActivityTaskCompleted successful)',
						array(
							'response' => (array)$taskResponse
						)
					);
				} else {
					$this->log(
						'error',
						'Activity failed (RespondActivityTaskCompleted failed)',
						array(
							'request' => $taskResponse,
							'response' => $completeResponse
						)
					);
				}
			} else {
				$this->log(
					'error',
					'Activity task class not found',
					array(
						'class' => $class
					)
				);
			}
		} catch (\Exception $e) {
			$this->log(
				'alert',
				'Exception when attempting to run activity: '.get_class($e).' - '.$e->getMessage(),
				array(
					'trace' => $e->getTrace()
				)
			);
		}
	}

	/**
	 * Get our db connection
	 *
	 * @access public
	 * @param string $key The key of the DB connection
	 * @return Doctrine\DBAL\Connection
	 */
	public function getDb($key) {
		return $this->amazonClass->getDb($key);
	}
}
