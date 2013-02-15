<?php
/**
 * User: Aaron Scherer
 * Date: 2/13/13
 */
namespace Uecode\Bundle\AmazonBundle\Exception;

class InvalidConfigurationException extends \Exception
{
	public function __construct( $message )
	{
		return parent::__construct( $message );
	}
}
