<?php

/**
 * @package amazon-bundle
 * @copyright (c) 2013 Undeground Elephant
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

namespace Uecode\Bundle\AmazonBundle\Service;

// Symfony
use Monolog\Logger;
use Symfony\Component\Yaml\Yaml;

// Uecode
use \Uecode\Bundle\UecodeBundle\Component\Config;
use \Uecode\Bundle\AmazonBundle\Factory\AmazonFactory;

// AmazonBundle Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\ClassNotFoundException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

class AmazonService
{

	/**
	 * @var Uecode\Bundle\AmazonBundle\Factory\AmazonFactory[]
	 */
	private $factories = array();

	private $config;
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

		$file = __DIR__ . '/../Resources/config/models.yml';
		$this->modelConfig = new Config((array)Yaml::parse($file));
	}

	public function getAmazonService($awsServiceName, $configConnectionKey, array $awsServiceOptions = array())
	{
		$class = $this->getAWSClass($awsServiceName);

		if (!$class) {
			throw new ClassNotFoundException($awsServiceName);
		}

		/** @var $config array Merge given configs with account configs */
		$this->config->setItems(array('aws_options' => $awsServiceOptions));

		$config = $this->config->all();

		$object = new $class();

		$amazonObject = $object->buildAmazonObject($config['accounts']['connections'][$configConnectionKey]);

		// Check to make sure its a valid Amazon object
		if (!($amazonObject instanceof \CFRuntime)) {
			throw new InvalidClassException('Amazon object could not be built.');
		}

		$object->setAmazonObject($amazonObject);

		// TODO add support for v2

		$object->setLogger($this->logger);

		return $object;
	}

	private function getAWSClass($className)
	{
		foreach ($this->modelConfig->all()['model'] as $modelName => $class) {
			if ($className === $modelName) {
				return $class;
			}
		}

		return null;
	}
}
