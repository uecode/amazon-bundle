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

namespace Uecode\Bundle\AmazonBundle\Component;

// Symfony (and related)
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;

// Models
use \Uecode\Bundle\AmazonBundle\Model\AmazonInterface;

// Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Amazon Bundle Components
use \Uecode\Bundle\AmazonBundle\Component\AbstractAmazonComponent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\ActivityWorker;

// Uecode Bundle Components
use \Uecode\Bundle\UecodeBundle\Component\Config;

use \AmazonSWF as SWF;

/**
 * @todo this class should encapsulate swf, not extend it.
 */
class SimpleWorkflow extends AbstractAmazonComponent
{

	/**
	 * @var bool Defines whether or not initialize() has been ran.
	 */
	private $initialized = false;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var
	 */
	protected $workflow;

	/**
	 * @var Logger Logger instance
	 * @access  protected
	 */
	protected $logger;

	/**
	 * @var Symfony container
	 */
	private $container;

	/*
	 * inherit
	 */
	public function buildAmazonObject(array $options)
	{
		return new \AmazonSWF($options);
	}

	/**
	 * Load a decider
	 *
	 * @param string $domain Domain name to register workflow in
	 * @param string $name Workflow name used for registration
	 * @param string $workflowVersion Workflow version used for egistration and finding decider related classes.
	 * @param string $activityVersion Activity version used for activity registration and finding activity related classes.
	 * @param string $taskList Task list to poll on
	 * @return DeciderWorker
	 */
	public function loadDecider($domain, $name, $workflowVersion, $activityVersion, $taskList)
	{
		return new DeciderWorker($this, $domain, $name, $workflowVersion, $activityVersion, $taskList);
	}

	public function loadActivityWorker($domain, $taskList, $activityVersion, $identity = null)
	{
		return new ActivityWorker($this, $domain, $taskList, $activityVersion, $identity);
	}

	/**
	 * Initializes the current object
	 *
	 * @param Config $config
	 * @return void
	 */
	public function initialize(Config $config)
	{
		if ($this->getInitialized()) {
			return;
		}

		$this->initializeConfigs($config);
		$this->setInitialized();

		return;
	}

	/**
	 * Initialize Configs
	 *
	 * @param Config $config
	 * @return void
	 */
	function initializeConfigs(Config $config)
	{
		$this->setConfig($config);
		$this->validateConfigs();
	}


	/**
	 * Validates $this->configs. Should be called within initialize
	 *
	 * @throws InvalidConfigurationException
	 * @return bool
	 */
	public function validateConfigs()
	{
	}

	/**
	 * Should be called at the end of initialize to show that the class has been initialized.
	 *
	 * @param bool $bool
	 * @return void
	 */
	public function setInitialized($bool = true)
	{
		$this->initialized = $bool;
	}

	/**
	 * Should return whether or not the initialize function has been ran.
	 *
	 * @return bool
	 */
	public function getInitialized()
	{
		return $this->initialized;
	}

	/**
	 * @param Config $config
	 * @return void
	 */
	public function setConfig(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
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

	/**
	 * Set container
	 *
	 * @access public
	 * @param Container $container Service container
	 */
	public function setContainer(Container $container) {
		$this->container = $container;
	}

	/**
	 * Get container
	 *
	 * @access public
	 * return Container
	 */
	public function getContainer() {
		return $this->container;
	}
}
