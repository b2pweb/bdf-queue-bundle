<?php

namespace Bdf\QueueBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Register all the objects provide a factory for receivers.
 */
final class RegisterReceiverFactoryPass implements CompilerPassInterface
{
    const CONFIGURATOR_TAG_NAME = 'bdf_queue.receiver_factory';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $factory = $container->getDefinition('bdf_queue.receiver.factory');

        foreach ($container->findTaggedServiceIds(self::CONFIGURATOR_TAG_NAME) as $serviceId => $tag) {
            $factory->addMethodCall('addFactory', [
                new Expression("service('".addslashes($serviceId)."').getReceiverNames()"),
                [new Reference($serviceId), 'create'],
            ]);
        }
    }
}
