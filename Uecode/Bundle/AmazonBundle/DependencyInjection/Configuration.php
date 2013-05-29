<?php

/**
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author Aaron Scherer
 *
 * Copyright 2013 Underground Elephant
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
								->useAttributeAsKey( 'name' )
								->prototype( 'array' )
									->children()
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
							->arrayNode('cron')
								->children()
									->arrayNode('deciders')
										->requiresAtLeastOneElement()
										->useAttributeAsKey('name')
											->prototype( 'array' )
											->children()
												->integerNode('count')
													->isRequired()
												->end()
											->end()
										->end()
									->end()
									->arrayNode('activities')
										->useAttributeAsKey('name')
											->prototype( 'array' )
											->children()
												->integerNode('count')
													->isRequired()
												->end()
											->end()
										->end()
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
