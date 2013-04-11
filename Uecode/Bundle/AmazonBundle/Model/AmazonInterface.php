<?php

/**
 * @package amazon-bundle
 * @author Aaron Scherer
 * @copyright (c) 2013 Undeground Elephant
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

namespace Uecode\Bundle\AmazonBundle\Model;

use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;

use \Uecode\Bundle\UecodeBundle\Component\Config;

interface AmazonInterface
{

	/**
	 * Initializes the current object
	 *
	 * @param Config $config
	 * @return void
	 */
	function initialize( Config $config );

	/**
	 * Initialize Configs
	 *
	 * @param Config $config
	 * @return void
	 */
	function initializeConfigs( Config $config );

	/**
	 * Validates $this->configs. Should be called within initialize
	 *
	 * @throws InvalidConfigurationException
	 * @return bool
	 */
	function validateConfigs( );

	/**
	 * @param Config $config
	 * @return void
	 */
	function setConfig( Config $config );

	/**
	 * @return Config
	 */
	function getConfig( );

	/**
	 * Should be called at the end of initialize to show that the class has been initialized.
	 *
	 * @param bool $bool
	 * @return void
	 */
	function setInitialized( $bool = true );

	/**
	 * Should return whether or not the initialize function has been ran.
	 *
	 * @return bool
	 */
	function getInitialized();
}
