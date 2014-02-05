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
namespace Uecode\Bundle\AmazonBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class LoggerCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = $container->findTaggedServiceIds('uecode_amazon.instance');
        foreach ($serviceIds as $serviceId) {
            $config = $container->getParameter($serviceId . '.config');
            if ($config['logging']['enabled']) {
                $this->addLogging($serviceId, $config, $container);
            }
        }
    }

    private function addLogging($serviceId, $account, ContainerBuilder $container)
    {
        if (!$container->hasDefinition($account['logging']['handler_id'])) {
            throw new \InvalidArgumentException(sprintf(
                "The logging handler `%s` does not exist within the container.",
                $account['logging']['handler_id']
            ));
        }

        $handler = new Reference($account['logging']['handler_id']);

        $logger = $container->setDefinition(
            'uecode_amazon.logger.' . $account['name'] . '.logger',
            new Definition('Monolog\\Logger', ['aws_' . $account['name']])
        );
        $logger->addMethodCall('pushHandler', [$handler]);

        $container->setDefinition(
            'uecode_amazon.logger.' . $account['name'] . '.adapter',
            new Definition('Guzzle\\Common\\Log\\MonologLogAdapter', [$logger])
        )
            ->setPublic(false);

        $container->setDefinition(
            'uecode_amazon.logger.' . $account['name'] . '.plugin',
            new Definition('Guzzle\\Plugin\\Log\\LogPlugin', [
                new Reference('uecode_amazon.logger.' . $account['name'] . '.adapter')
            ])
        )
            ->setPublic(false);

        $container->getDefinition($serviceId)
            ->addMethodCall('addSubscriber', [new Reference('uecode_amazon.logger.' . $account['name'] . '.plugin')]);
    }
}
