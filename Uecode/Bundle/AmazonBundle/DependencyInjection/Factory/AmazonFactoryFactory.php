<?php

namespace Uecode\Bundle\AmazonBundle\DependencyInjection\Factory;

use \Uecode\Bundle\UecodeBundle\Component\Config;
use \Uecode\Bundle\AmazonBundle\Factory\AmazonFactory;

class AmazonFactoryFactory
{
	public function build( $config )
	{
		$config = new Config( $account );
		$factory = new AmazonFactory( $config );

		return $factory;
	}
}
