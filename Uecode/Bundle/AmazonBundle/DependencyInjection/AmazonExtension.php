<?php
/**
 * @author Aaron Scherer
 * @date 10/8/12
 */
namespace Uecode\Bundle\AmazonBundle\DependencyInjection;

use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\DependencyInjection\Loader;
use \Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Uecode  Extension
 */
class AmazonExtension extends Extension
{
	/**
	 * {@inheritdoc}
	 */
	public function load( array $configs, ContainerBuilder $container )
	{
		$configuration = new Configuration( $container->getParameter( 'kernel.debug' ) );
		$config = $this->processConfiguration( $configuration, $configs );

		$this->setParameters( $container, $config );

		foreach( $config[ 'uecode' ][ 'amazon' ][ 'accounts' ]['connections'] as $name => $account ) {
			$account ['name' ] = $name;
			$config = new Config( $account );
			$factory = new \Uecode\Bundle\AmazonBundle\Factory\AmazonFactory( $config );
			$container->setParameter( trim( 'uecode.amazon.factory.' . $name ), $factory );
		}
	}

	public function getConfiguration(array $config, ContainerBuilder $container)
	{
		return new Configuration( $container->getParameter( 'kernel.debug' ) );
	}

	private function setParameters( ContainerBuilder $container, array $configs, $prefix = 'uecode' )
	{
		foreach( $configs as $key => $value )
		{
			if( is_array( $value ) )
			{
				$this->setParameters( $container, $configs[ $key ], ltrim( $prefix . '.' . $key, '.' ) );
				$container->setParameter(  ltrim( $prefix . '.' . $key, '.' ), $value );
			}
			else
			{
				$container->setParameter( ltrim( $prefix . '.' . $key, '.' ), $value );
			}
		}
	}
}
