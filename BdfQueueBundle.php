<?php

namespace Bdf\QueueBundle;

use Bdf\QueueBundle\FailerFactory\FailerFactory;
use Bdf\QueueBundle\FailerFactory\PrimeFailerFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * BdfQueueBundle
 *
 * @author Seb
 */
class BdfQueueBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container)
            {
                // Enable prime failer
                if (PrimeFailerFactory::supported() && $container->has('prime')) {
                    $container->register(PrimeFailerFactory::class)
                        ->setClass(PrimeFailerFactory::class)
                        ->setArguments([new Reference('prime')])
                    ;

                    // @todo init command ?
                    $container->findDefinition(FailerFactory::class)
                        ->addMethodCall('register', [new Reference(PrimeFailerFactory::class)]);
                }
            }
        });
    }
}
