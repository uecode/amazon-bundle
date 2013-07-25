<?php 

/**
 * @package amazon-bundle
 * @author 
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
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

/**
 * For working w/ Amazon SNS
 *
 */
class SimpleNotificationService extends AbstractAmazonComponent
{

	/*
	 * inherit
	 * @return \AmazonSNS
	 */
	public function buildAmazonObject(array $options)
	{
		return new \AmazonSNS($options);
	}

	/**
	 * @param        $subject
	 * @param string $message
	 * @throws \Exception
	 */
	public function publish( $subject, $message = '' )
	{
		$sns = $this->getAmazonObject();
		if( is_null( $this->topic ) ) throw new \Exception( 'Must specifiy a topic' );
		$sns->publish(
			$this->topic,
			$message,
			array( 'Subject' => $subject )
		);
	}

	private function setTopic( $topic )
	{
		$this->topic = $topic;
		return $this;
	}

	private function setAWSKey( $key )
	{
		$this->account_key = $key;
		return $this;
	}

	private function setAWSSecret( $secret )
	{
		$this->account_secret = $secret;
		return $this;
	}
}
