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

use Uecode\Bundle\UecodeBundle\Component\Config;

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
		//$loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
		//$loader->load( 'services.yml' );

		foreach( $container->getParameter( 'uecode.amazon.accounts.connections' ) as $name => $account ) {
			$account ['name' ] = $name;
			$config = new Config( $account );
			$factory = new \Uecode\Bundle\AmazonBundle\Factory\AmazonFactory( $config );
			$container->set( trim( 'uecode.amazon.factory.' . $name ), $factory );
		}	
	}

	public function getXsdValidationBasePath()
	{
		return __DIR__ . '/../Resources/config/';
	}
}
