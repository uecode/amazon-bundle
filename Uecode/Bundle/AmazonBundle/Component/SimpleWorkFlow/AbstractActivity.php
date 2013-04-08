<?php

/**
 * Abstract activity type
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author John Pancoast
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

use Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\ActivityWorker;
use \CFResponse;

abstract class AbstractActivity
{
	/**
	 * @var string The version of this activity type (in this domain).
	 *
	 * @access protected
	 */
	protected $version = '1.0';

	/**
	 * Activity logic that gets executed when an activity worker assigns work
	 *
	 * Note that the activity is considered successful unless you explicitly return false.
	 * The string you return from the method is sent as the "result" field in the 
	 * RespondActivityTaskCompleted request.
	 *
	 * @abstract
	 * @access protected
	 * @param AbstractActivity $activity
	 * @param CFResponse $response The response received from polling amazon for an activity
	 * @return mixed A bool of false implies a failure, otherwise a string is the response.
	 */
	abstract protected function activityLogic(ActivityWorker $activity, CFResponse $response);

	/**
	 * Run activity logic
	 *
	 * @access public
	 * @param AbstractActivity $activity
	 * @param CFResponse $response The response received from polling amazon for an activity
	 * @return mixed A bool of false implies a failure, otherwise a string is the response.
	 */
	public function run(ActivityWorker $activity, CFResponse $response)
	{
		return $this->activityLogic($activity, $response);
	}

	/**
	 * Get version
	 *
	 * @access public
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}
}
