<?php
namespace Uecode\Bundle\AmazonBundle\Service;

use \Uecode\Bundle\UecodeBundle\Component\Config;
use \Uecode\Bundle\AmazonBundle\Factory\AmazonFactory;

class AmazonService
{

	/**
	 * @var Uecode\Bundle\AmazonBundle\Factory\AmazonFactory[]
	 */
	private $factories = array();

	public function __construct( array $config )
	{
		if( !empty( $config ) ) {
			foreach( $config[ 'accounts' ][ 'connections' ] as $name => $account ) {
				$this->addFactory( $name, new AmazonFactory( $name, new Config( $config ) ) );
			}
		}
	}

	public function addFactory( $name, $factory )
	{
		if( array_key_exists( $name, $this->factories ) ) {
			throw new \Exception( sprintf( "The `%s` factory has already been added.", $name ) );
		}
		
		return $this->factories[ $name ] = $factory;
	}

	public function getFactory( $name )
	{
		if( !array_key_exists( $name, $this->factories ) ) {
			throw new \Exception( sprintf( "The `%s` factory does not exist.", $name ) );
		}
		
		return $this->factories[ $name ];
	}

	public function get( $name )
	{
		return $this->getFactory( $name );
	}

	public function __get( $name )
	{
		return $this->getFactory( $name );
	}
}
