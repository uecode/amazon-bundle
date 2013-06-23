<?php

/**
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author Aaron Scherer
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

namespace Uecode\Bundle\AmazonBundle\Component;

use \CFRuntime;

abstract class AbstractAmazonComponent
{
	/**
	 * @var CFRuntime
	 */
	protected $amazonObject;

	/**
	 * Build and return ana amazon object
	 *
	 * @abstract
	 * @access public
	 * @return CFRuntime
	 */
	abstract public function buildAmazonObject(array $options);

	/**
	 * @param CFRuntime $amazonObject
	 */
	public function setAmazonObject(CFRuntime $amazonObject)
	{
		$this->amazonObject = $amazonObject;
	}

	/**
	 * @return CFRuntime
	 */
	public function getAmazonClass()
	{
		return $this->amazonClass;
	}
}
