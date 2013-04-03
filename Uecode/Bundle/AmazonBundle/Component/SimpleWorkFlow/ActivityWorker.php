<?php
/**
 * Activity worker
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author Aaron Scherer, John Pancoast
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

// Amazon Components
use \Uecode\Bundle\AmazonBundle\Component\AmazonComponent;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidDeciderLogicException;

// Amazon Classes
use \AmazonSWF;
use \CFResponse as CFResponse;

class ActivityWorker extends AmazonComponent
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
		$this->setAmazonClass($swf);
		$this->taskList = $taskList;
		$this->identity = $identity;
	}

	public function run()
	{
		while (true) {
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
					$res = $this->runActivity($response);
				}
			}
		}
	}

	/**
	 * Given an activity worker response, run the activity
	 *
	 * @access protected
	 * @retur CFResponse
	 */
	public function runActivity(CFResponse $response)
	{
		$name = $response->body->activityType->name;
		$token = (string)$response->body->taskToken;
		$activityArr = $this->amazonClass->getActivityArray();
		$class = $activityArr['namespace'].'\\'.$name;
		if (class_exists($class))
		{
			$obj = new $class;
			$res = $obj->run($this, $response);
			if ($res !== false) {
				$opts = array(
					'taskToken' => $token
				);
				if (!empty($res)) {
					$opts['response'] = $res;
				}

				return $this->amazonClass->respond_activity_task_completed($opts)->body;
			}
		}
	}

	public function debug($str)
	{
		return $this->amazonClass->debug($str);
	}
}
