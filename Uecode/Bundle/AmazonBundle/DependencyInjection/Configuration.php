<?php
/**
 * @author Aaron Scherer
 * @date 10/8/12
 */
namespace Uecode\Bundle\AmazonBundle\DependencyInjection;

use \Symfony\Component\Config\Definition\Builder\TreeBuilder;
use \Symfony\Component\Config\Definition\ConfigurationInterface;


/**
 * Configuration for the  Bundle
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode    = $treeBuilder->root( 'uecode' );

		$rootNode
			->children()
			//->append( $this->addNode() )
			->end();

		return $treeBuilder;
	}

	/**
	 * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
	 /
	public function addNode()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode    = $treeBuilder->root( 'gearman' );

		$rootNode
			->children()
				->append( $this->addGearmanClient() )
				->append( $this->addGearmanServer() )
			->end()
		;
		return $rootNode;
	}
	*/
}
