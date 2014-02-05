<?php
/**
 * @package       amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author        Aaron Scherer
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
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for the  Bundle
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @return ArrayNodeDefinition
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('uecode_amazon');

        $rootNode->append($this->addAccount());

        return $treeBuilder;
    }

    private function addAccount()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('accounts');

        $rootNode
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('key')
                        ->isRequired()
                    ->end()
                    ->scalarNode('secret')
                        ->isRequired()
                    ->end()
                    ->scalarNode('region')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $rootNode;
    }
}
