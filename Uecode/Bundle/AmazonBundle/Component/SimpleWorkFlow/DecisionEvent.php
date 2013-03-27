<?php
/**
 * A decision event that all decision events must extend.
 *
 * @author John Pancoast
 * @date 2013-03-25
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

class DecisionEvent
{
	/**
	 * @var string Event title.
	 *
	 * @access protected
	 */
	protected $title;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		// set the title to class name for now
		$this->setTitle(basename(str_replace('\\', '/', get_class($this))));
	}

	/**
	 * Set the event title
	 *
	 * @param string $title
	 * @access public
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Get the event title
	 *
	 * @access public
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
}