<?php

/**
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author Aaron Scherer
 */

namespace Uecode\Bundle\AmazonBundle\DependencyInjection;

use \Symfony\Component\Config\Definition\Builder\TreeBuilder;
use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use \Uecode\Bundle\UecodeBundle\DependencyInjection\ConfigurationInterface;

/**
 * Configuration for the  Bundle
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * Append configuration data by reference to the given rootNode
	 *
	 * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
	 * @return mixed
	 */
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
		$rootNode->append( $this->addSimpleWorkflow() );

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
								->isRequired()
							->end()
							->scalarNode( 'secret' )
								->isRequired()
							->end()
						->end()
					->end()
				->end()
			->end()
		->end();

		return $rootNode;
	}

	private function addSimpleWorkflow()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode    = $treeBuilder->root( 'simpleworkflow' );

		$rootNode
			->children()
				->arrayNode( 'domains' )
					->requiresAtLeastOneElement()
					->useAttributeAsKey( 'domain' )
					->prototype( 'array' )
						->children()
							->arrayNode('workflows')
								->requiresAtLeastOneElement()
								->useAttributeAsKey( 'workflow_config_key' )
								->prototype( 'array' )
									->children()
										->scalarNode( 'name' )
											->isRequired()
										->end()
										->scalarNode( 'version' )
											->isRequired()
										->end()
										->scalarNode( 'default_task_list' )
											->isRequired()
										->end()
										->scalarNode( 'history_event_namespace' )
											->isRequired()
										->end()
										->scalarNode( 'history_activity_event_namespace' )
											->isRequired()
										->end()
									->end()
								->end()
							->end()
							->arrayNode('activities')
								->children()
									->scalarNode( 'namespace' )
										->isRequired()
									->end()
									->scalarNode( 'directory' )
										->isRequired()
									->end()
									->scalarNode( 'default_task_list' )
										->isRequired()
									->end()
								->end()
							->end()
						->end()
					->end()
				->end()
			->end()
		->end();

		return $rootNode;
	}
}
