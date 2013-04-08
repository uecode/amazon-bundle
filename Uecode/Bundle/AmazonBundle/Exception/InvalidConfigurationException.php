<?php

/**
 * @package amazon-bundle
 * @author Aaron Scherer
 * @copyright (2) 2013 Undeground Elephant
 */

namespace Uecode\Bundle\AmazonBundle\Exception;

class InvalidConfigurationException extends \Exception
{
	public function __construct( $message )
	{
		return parent::__construct( $message );
	}
}
