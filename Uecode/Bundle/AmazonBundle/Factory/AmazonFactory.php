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

namespace Uecode\Bundle\AmazonBundle\Factory;

// Amazon Classes
use CFRuntime;

// Symfony classes
use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;

// AmazonBundle Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\ClassNotFoundException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Uecode Components
use \Uecode\Bundle\UecodeBundle\Component\Config;

// Uecode Amazon Classes
use Uecode\Bundle\AmazonBundle\Model\AmazonInterface;

class AmazonFactory implements FactoryInterface
{
	/**
	 * @var string This factories name
	 * @TODO this is only here due to current architecture. May change this up.
	 */
	private $name;

	/**
	 * @var Config Config for models
	 */
	private $modelConfig;

	/**
	 * @var Config Config for amazon related
	 */
	private $config;

	/**
	 * @var Logger Logger instance
	 * @accese private
	 */
	private $logger;

	public function __construct($name, Config $config)
	{
		$this->name = $name;
		$this->setConfig( $config );

		$file = __DIR__ . '/../Resources/config/models.yml';
		$this->setModelConfig(new Config(Yaml::parse($file)));
	}

	/**
	 * Searches for the amazon class for the given class name and builds it.
	 * If there is a Uecode Extension for this, loads that instead and initializes.
	 *
	 * @param string $className Amazon Class name
	 * @param array $options Arguments for the Amazon Class
	 * @param Container $container The service container
	 * @return CFRuntime|bool Returns the amazon class
	 * @throws ClassNotFoundException|InvalidClassException
	 */
	public function build($className, array $options = array(), Container $container)
	{
		$class = $this->checkAmazonClass($className);
		if ($class !== false) {

			/** @var $config array Merge given configs with account configs */
			$this->config->setItems($options);

			$config = $this->config->all();

			$object = new $class($config['accounts']['connections'][$this->name]);

			// Check to make sure its a valid Amazon object
			if (!($object instanceof CFRuntime)) {
				throw new InvalidClassException($class);
			}

			// @TODO Add check for Amazon API v2

			if ($object instanceof AmazonInterface) {
				$object->initialize($this->config);
			}

			$object->setLogger($this->getLogger());
			$object->setContainer($container);

			return $object;
		}

		throw new ClassNotFoundException($className);
	}

	/**
	 * Checks to see if the Amazon Class given exists.
	 * Testing if it has Amazon in the name as well
	 *
	 * @param string $class
	 * @param bool $checkAmazon
	 * @return bool|string
	 */
	public function checkAmazonClass($class, $checkAmazon = true)
	{
		// Check to see if we have the Amazon class
		if (class_exists($class)) {

			// If we do, check to see if we have a Uecode version of this class and replace $class with that
			$this->checkUecodeClass($class);

			return $class;
		}

		// If $class doesnt exist, check for Amazon$class
		if ($checkAmazon) {
			return $this->checkAmazonClass('Amazon' . $class, false);
		}

		return false;
	}

	/**
	 * Checks to see if there is a Uecode extension of the given class
	 * If there is renames $className
	 *
	 * @param string $className
	 * @throws InvalidClassException
	 */
	public function checkUecodeClass(&$className)
	{

		// Run through the configs
		foreach ($this->getModelConfig()->all() as $modelGroup) {

			foreach($modelGroup as $model) {
				// If we have an override for the amazon class, replace it
				if ($className === $model['amazon_class']) {
					$className = $model['uecode_class'];
				}
			}
		}

		// If the given class doesn't exist, throw an error.
		// This shouldn't happen
		if (!class_exists($className)) {
			throw new InvalidClassException($className);
		}
	}

	/**
	 * @param Config $config
	 * @return AmazonFactory
	 */
	public function setConfig(Config $config)
	{
		$this->config = $config;

		return $this;
	}

	/**
	 * @return \Uecode\Bundle\UecodeBundle\Component\Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @param Config $modelConfig
	 * @return AmazonFactory
	 */
	public function setModelConfig(Config $modelConfig)
	{
		$this->modelConfig = $modelConfig;

		return $this;
	}

	/**
	 * @return \Uecode\Bundle\UecodeBundle\Component\Config
	 */
	public function getModelConfig()
	{
		return $this->modelConfig;
	}

	/**
	 * Set the logger
	 *
	 * @param Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Get the logger
	 *
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}
