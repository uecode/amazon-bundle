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

//Guzzle
use Guzzle\Log;
use Guzzle\Service\Builder\ServiceBuilder;

// Symfony (and related)
use Monolog\Logger;

// Uecode Bundle Components
use \Uecode\Bundle\UecodeBundle\Component\Config;
use \Uecode\Bundle\AmazonBundle\Exception\ClassNotFoundException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// AWS
use \Aws\Common\Aws;

// Amazon
//use \CFRuntime;

abstract class AbstractAmazonComponent
{
	/**
	 * @var ServiceBuilder
	 */
	protected $amazonObject;

	/**
	 * @var object
	 */
	protected $amazonServiceObject;

	/**
	 * @var bool Have configs been initialized
	 *
	 * @param private
	 */
	private $initialized = false;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var Logger Logger instance
	 * @access  protected
	 */
	protected $logger;

	/**
	 * @var LogPlugin log_plugin
	 * @access  protected
	 */
	protected $log_plugin;

	/**
	 * Build and return an amazon object
	 *
	 * @abstract
	 * @access public
	 * @return ServiceBuilder Amazon object
	 */
	abstract public function buildAmazonObject(array $options);

	/**
	 * Build and return an amazon service object
	 *
	 * @abstract
	 * @access public
	 * @return object Amazon service object
	 */
	abstract public function buildAmazonServiceObject($awsServiceName);

	/**
	 *
	 * @access public
	 * @param ServiceBuilder Amazon object
	 * @return AbstractAmazonComponent
	 */
	public function setAmazonObject(ServiceBuilder $amazonObject)
	{
		$this->amazonObject = $amazonObject;
		return $this;
	}

	/**
	 * @return ServiceBuilder
	 */
	public function getAmazonObject()
	{
		return $this->amazonObject;
	}

	/**
	 *
	 * @access public
	 * @param object Amazon Service object
	 * @return object
	 */
	public function setAmazonServiceObject($amazonServiceObject)
	{
		$this->amazonServiceObject = $amazonServiceObject;
		return $this;
	}

	/**
	 * @return object
	 */
	public function getAmazonServiceObject()
	{
		return $this->amazonServiceObject;
	}

	/**
	 * Initializes the current object
	 *
	 * @param Config $config
	 * @return AbstractAmazonComponent
	 */
	public function initialize(Config $config)
	{
		if ($this->getInitialized()) {
			return;
		}

		$this->initializeConfigs($config);
		$this->setInitialized();

		return $this;
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
     * Build Guzzle Log Plugin
     *
     * @param object $log_adapter Guzzle Log Adapter
     * @param object $formatter MessageFormatter
     * @access public
     * @return Log Plugin
     */
    public function buildLogPlugin( $log_adapter = null, $formatter = null ) 
    {
        if ( is_null( $log_adapter ) )
        {
            $adapter = 'Guzzle\Log\\' . $this->config->get('log_adapter');
            $log_adapter = new $adapter( $this->logger );
        }

        return new \Guzzle\Plugin\Log\LogPlugin( $log_adapter, $formatter );
    }

	/**
	 * Set the log plugin
	 *
	 * @param  LogPLugin log_plugin
	 * @return AbstractAmazonComponent
	 */
	public function setLogPlugin( \Guzzle\Plugin\Log\LogPlugin $log_plugin)
	{
		$this->log_plugin = $log_plugin;
		return $this;
	}

	/**
	 * Get the log plugin
	 *
	 * @return LogPlugin
	 */
	public function getLogPlugin()
	{
		return $this->log_plugin;
	}

	/**
	 * Set the logger
	 *
	 * @param Logger $logger
	 * @return AbstractAmazonComponent
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
		return $this;
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
