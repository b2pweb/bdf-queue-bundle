<?php

namespace Bdf\QueueBundle\DependencyInjection\Compiler;

use Bdf\QueueBundle\ConnectionFactory\ConnectionDriverFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register all the custom factory for connection
 */
final class DriverFactoryPass implements CompilerPassInterface
{
    const CONFIGURATOR_TAG_NAME = 'bdf_queue.driver_configurator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $factory = $container->getDefinition(ConnectionDriverFactory::class);

        foreach ($container->findTaggedServiceIds(self::CONFIGURATOR_TAG_NAME) as $serviceId => $tag) {
            $factory->addMethodCall('registerConfigurator', [new Reference($serviceId)]);
        }
    }
}
