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

// Uecode
use \Uecode\Bundle\UecodeBundle\Component\Config;
use \Uecode\Bundle\AmazonBundle\Factory\AmazonFactory;

class AmazonService
{

	/**
	 * @var Uecode\Bundle\AmazonBundle\Factory\AmazonFactory[]
	 */
	private $factories = array();

	/**
	 * Constructor
	 *
	 * @param array  $config
	 * @param Logger $logger
	 */
	public function __construct(array $config, Logger $logger = null)
	{
		if(!empty($config)) {
			foreach($config['accounts']['connections'] as $name => $account) {
				$factory = new AmazonFactory($name, new Config($config));
				$factory->setLogger($logger);
				$this->addFactory($name, $factory);
			}
		}
	}

	public function addFactory($name, $factory)
	{
		if( array_key_exists($name, $this->factories)) {
			throw new \Exception(sprintf("The `%s` factory has already been added.", $name));
		}
		
		return $this->factories[$name] = $factory;
	}

	public function getFactory($name)
	{
		if( !array_key_exists($name, $this->factories)) {
			throw new \Exception(sprintf("The `%s` factory does not exist.", $name));
		}
		
		return $this->factories[$name];
	}

	public function get($name)
	{
		return $this->getFactory($name);
	}

	public function __get($name)
	{
		return $this->getFactory($name);
	}
}
