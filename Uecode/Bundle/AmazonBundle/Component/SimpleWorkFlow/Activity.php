<?php
/**
 * Abstract activity type
 *
 * @author John Pancoast
 * Date: 2/13/13
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

/*
// Amazon Components
use \Uecode\Bundle\AmazonBundle\Component\AmazonComponent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\HistoryEventIterator;/
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\State\DeciderWorkerState;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
use \Uecode\Bundle\AmazonBundle\Exception\SimpleWorkFlow\InvalidEventTypeException;

// Events
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\AbstractHistoryEvent;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;
*/

/**
 * @todo TODO rename to AbstractActivity
 */
abstract class Activity
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
	 * @abstract
	 * @access protected
	 */
	abstract protected function activityLogic();

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
