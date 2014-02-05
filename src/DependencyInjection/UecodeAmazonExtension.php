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

use Symfony\Component\Config\FileLocator;
use \Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use \Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use \Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Uecode  Extension
 */
class UecodeAmazonExtension extends Extension
{

    private $logging;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('config.yml');

        $this->buildServicesForAccounts($config['accounts'], $container);
    }

    /**
     * @param array            $accounts
     * @param ContainerBuilder $container
     */
    private function buildServicesForAccounts(array $accounts, ContainerBuilder $container)
    {
        foreach ($accounts as $name => $account) {
            $account['name'] = $name;
            $this->createAWSDefinition($account, $container);
            $container->setParameter('uecode_amazon.instance.' . $name . '.config', $account);
        }
    }

    /**
     * @param array            $account
     * @param ContainerBuilder $container
     */
    private function createAWSDefinition(array $account, ContainerBuilder $container)
    {
        $definition = $container->setDefinition(
            'uecode_amazon.instance.' . $account['name'],
            new Definition('%uecode_amazon.instance.class%', [$account])
        );

        $definition->setFactoryClass('%uecode_amazon.factory.class%')
            ->setFactoryMethod($container->getParameter('uecode_amazon.factory.method'))
            ->addTag('uecode_amazon.instance');


        $container->setAlias('aws.' . $account['name'], 'uecode_amazon.instance.' . $account['name']);
    }
}
