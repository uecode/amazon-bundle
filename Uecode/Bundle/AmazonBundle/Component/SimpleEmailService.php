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
 * For working w/ Amazon SES
 *
 */
class SimpleEmailService extends AbstractAmazonComponent
{
	/*
	 * inherit
	 * @return \AmazonSES
	 */
	public function buildAmazonObject(array $options)
	{
		return new \AmazonSES($options);
	}

	public function sendMail($from, $to, $subject, $html, $text)
	{
		$ses = $this->getAmazonObject();
		if( is_null( $ses) ) throw new \Exception( 'SES Object is Null' );
		$response = $ses->send_email( $from, array( 'ToAddresses' => array( $to,),), array( 'Subject' => array( 'Data' => $subject, 'Charset' => 'UTF-8'), 'Body' => array( 'Text' => array( 'Data' => $text, 'Charset' => 'UTF-8'), 'Html' => array( 'Data' => $html, 'Charset' => 'UTF-8'))) );
		return $response;
	}

}
