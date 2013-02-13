<?php
/**
 * @author Aaron Scherer
 * @date 10/8/12
 */
namespace Uecode\Bundle\AmazonBundle\DependencyInjection;

use \Symfony\Component\Config\Definition\Builder\TreeBuilder;
use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Uecode\Bundle\UecodeBundle\DependencyInjection\ConfigurationInterface;

/**
 * Configuration for the  Bundle
 */
class Configuration implements ConfigurationInterface
{
	public function appendTo( ArrayNodeDefinition &$rootNode )
	{
		$rootNode->append( $this->addAmazonNode() );
	}

	/**
	 * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
	 */
	private function addAmazonNode()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode    = $treeBuilder->root( 'amazon' );

		$rootNode->append( $this->addAmazonAccount() );

		return $rootNode;
	}

    private function addAmazonAccount( )
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root( 'accounts' );

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
