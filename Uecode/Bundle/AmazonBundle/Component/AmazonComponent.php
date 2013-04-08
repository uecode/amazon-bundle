<?php

/**
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author Aaron Scherer
 */

namespace Uecode\Bundle\AmazonBundle\Component;

use \CFRuntime;

class AmazonComponent
{
	/**
	 * @var CFRuntime
	 */
	protected $amazonClass;

	/**
	 * @param CFRuntime $amazonClass
	 */
	public function setAmazonClass( CFRuntime $amazonClass )
	{
		$this->amazonClass = $amazonClass;
	}

	/**
	 * @return CFRuntime
	 */
	public function getAmazonClass()
	{
		return $this->amazonClass;
	}
}
