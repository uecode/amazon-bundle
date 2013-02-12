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
            ->append( $this->addAmazonNode() )
			->children()
			->end();

		return $treeBuilder;
	}

	/**
	 * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
	 */
	private function addAmazonNode()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode    = $treeBuilder->root( 'amazon' );

		$rootNode
			->children()
				->append( $this->addAmazonAccount() )
			->end()
		;
		return $rootNode;
	}

    private function addAmazonAccount( )
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root( 'account' );

		$rootNode
            ->children()
                ->arrayNode( 'connections' )
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey( 'name' )
                    ->prototype( 'array' )
                        ->children()
                            ->scalarNode( 'key' )
                                ->required()
                            ->end()
                            ->scalarNode( 'secret' )
                                ->required()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $rootNode;
    }
}
