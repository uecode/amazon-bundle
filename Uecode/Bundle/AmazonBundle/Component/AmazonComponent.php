<?php

/**
 * @package amazon-bundle
 * @author John Pancoast, Aaron Scherer
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

// Amazon Bundle Components
use \Uecode\Bundle\AmazonBundle\Component\AbstractAmazonComponent;

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

/**
 *
 * 
 */
class AmazonComponent extends AbstractAmazonComponent
{
	/*
	 * inherit
     * @param array options
	 * @return ServiceBuilder
	 */
	public function buildAmazonObject(array $options)
	{
        $options = sizeof( $options ) > 0 ? $options : $this->config->get('custom_config_file');

        $aws_object = Aws::factory($options);

        if ( gettype( $aws_object ) == 'object' )
            return $aws_object;
        else
            throw new InvalidClassException('AWS Object Inavlid');
	}

	/*
	 * inherit
     * @param array options
	 * @return object
	 */
	public function buildAmazonServiceObject($awsServiceName)
	{
        return $this->getAmazonObject()->get( $awsServiceName );
	}

	/**
	 * Call SDK method
	 *
	 * Currently working w/ v1 of SDK
	 *
	 * @access public
	 * @param string $command SDK command
	 * @param array $options SDK command options
	 */
	public function callSDK($command, array $options)
	{
		return $this->getAmazonServiceObject()->{$command}($options);
	}

}
