<?php

namespace Bdf\QueueBundle;

use Bdf\QueueBundle\DependencyInjection\Failer\RegisterFailerDriverPass;
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
        $container->addCompilerPass(new RegisterFailerDriverPass());
    }
}
