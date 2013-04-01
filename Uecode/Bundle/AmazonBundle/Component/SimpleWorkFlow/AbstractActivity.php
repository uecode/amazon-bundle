<?php
/**
 * Abstract activity type
 *
 * @author John Pancoast
 * @date 2013-03-31
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

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
