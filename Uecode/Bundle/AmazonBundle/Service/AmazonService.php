<?php

/**
 * @package amazon-bundle
 * @copyright (c) 2013 Undeground Elephant
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

namespace Uecode\Bundle\AmazonBundle\Service;

// Symfony
use Monolog\Logger;
use Symfony\Component\Yaml\Yaml;

// Uecode
use \Uecode\Bundle\UecodeBundle\Component\Config;

// AmazonBundle Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\ClassNotFoundException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

/**
 * Service for loading Amazon services
 *
 * @author John Pancoast
 */
class AmazonService
{
	/**
	 * @var Config
	 *
	 * @access private
	 */
	private $config;

	/**
	 * @var Logger
	 *
	 * @access private
	 */
	private $logger;

	/**
	 * Constructor
	 *
	 * @param array  $config
	 * @param Logger $logger
	 */
	public function __construct($config, Logger $logger = null)
	{
		$this->config = new Config($config);
		$this->logger = $logger;
	}

	/**
	 * Get an amazon servive
	 *
	 * @access public
	 * @param string $awsserviceName The service to load
	 * @param string $configConnectionKey A config key specifying amazon connection to use (relative to uecode.amazon.accounts.connections)
	 * @param array $awsServiceOptions Options needed for the service
	 * @return AbstractAmazonComponent (child of it)
	 */
	public function getAmazonService($awsServiceName, $configConnectionKey, array $awsServiceOptions = array())
	{
		$class = $this->getAmazonClass($awsServiceName);

		if (!$class) {
			throw new ClassNotFoundException($awsServiceName);
		}

		/** @var $config array Merge given configs with account configs */
		$this->config->setItems(array('aws_options' => $awsServiceOptions));

		$config = $this->config->all();

		$object = new $class();
		$object->initialize($this->config)
		       ->setAmazonObject($object->buildAmazonObject($config['accounts']['connections'][$configConnectionKey]))
		       ->setLogger($this->logger);

		return $object;
	}

	/**
	 * Find amazon class location
	 *
	 * @access private
	 * @param string $className The name of class to locate
	 * @return mixed string|null
	 */
	private function getAmazonClass($className)
	{
		foreach ($this->config->all()['classes'] as $cName => $class) {
			if ($className === $cName) {
				return $class;
			}
		}

		return null;
	}
}
