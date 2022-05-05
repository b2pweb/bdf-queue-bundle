<?php

namespace Bdf\QueueBundle;

use Bdf\QueueBundle\DependencyInjection\Compiler\DriverFactoryPass;
use Bdf\QueueBundle\DependencyInjection\Compiler\RegisterFailerDriverPass;
use Bdf\QueueBundle\DependencyInjection\Compiler\RegisterReceiverFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        $container->addCompilerPass(new DriverFactoryPass());
        $container->addCompilerPass(new RegisterFailerDriverPass());
        $container->addCompilerPass(new RegisterReceiverFactoryPass());
    }
}
