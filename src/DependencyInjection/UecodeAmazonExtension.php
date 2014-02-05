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
        $factory = $container->setDefinition(
            'uecode_amazon.factory',
            new Definition('%uecode_amazon.factory.class%')
        );
        $factory->setPublic(false);

        foreach ($accounts as $name => $account) {
            $account['name'] = $name;
            $this->createAWSDefinition($account, $container);
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

        $definition->setFactoryService('uecode_amazon.factory')
            ->setFactoryMethod('%uecode_amazon.factory.method%');

        if ($account['logging']['enabled']) {
            $this->addLogging($account, $definition, $container);
        }

        $container->setAlias('aws.' . $account['name'], $definition);
    }

    private function addLogging(array $account, Definition $definition, ContainerBuilder $container)
    {
        if (!$container->hasDefinition($account['logging']['logger_id'])) {
            throw new \InvalidArgumentException(sprintf(
                "The logger `%s` does not exist within the container.",
                $account['logging']['logger_id']
            ));
        }

        $logger = new Reference($account['logging']['logger_id']);

        $container->setDefinition(
            'uecode_amazon.logger.' . $account['name'],
            new Definition('Guzzle\\Common\\Log\\MonologLogAdapter', [$logger])
        )
            ->setPublic(false);

        $container->setDefinition(
            'uecode_amazon.logger.plugin.' . $account['name'],
            new Definition('Guzzle\\Plugin\\Log\\LogPlugin', [
                new Reference('uecode_amazon.logger.' . $account['name'])
            ])
        )
            ->setPublic(false);

        $definition->addMethodCall('addSubscriber', [new Reference('uecode_amazon.logger.plugin.' . $account['name'])]);
    }
}
