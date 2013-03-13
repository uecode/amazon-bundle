<?php
namespace Uecode\Bundle\AmazonBundle\Service;

class AmazonService
{

	/**
	 * @var Uecode\Bundle\AmazonBundle\Factory\AmazonFactory[]
	 */
	private $factories = array();

	public function __construct( array $config )
	{
		if( !empty( $config ) ) {
			foreach( $config[ 'accounts' ][ 'connection' ] as $name => $key ) {
				$account[ 'name' ] = $name;
				$config = new Config( $account );
				$factory = new \Uecode\Bundle\AmazonBundle\Factory\AmazonFactory( $config );
				
				$container->setParameter( trim( 'uecode.amazon.factory.' . $name ), $factory );
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
