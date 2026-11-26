<?php

namespace Bdf\QueueBundle\Consumption\Receiver;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\ReceiverInterface;
use Bdf\QueueBundle\Consumption\ReceiverFactoryInterface;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetterInterface;

class ResetServicesFactory implements ReceiverFactoryInterface
{
    public function getReceiverNames(): array
    {
        return ['reset'];
    }

    public function create(ReceiverFactory $factory, ...$arguments): ReceiverInterface
    {
        if (!isset($arguments[0]) || !($arguments[0] instanceof ServicesResetter || $arguments[0] instanceof ServicesResetterInterface || $arguments[0] instanceof \Symfony\Component\DependencyInjection\ServicesResetter)) {
            throw new \LogicException(sprintf('First argument of %s should be an instance of %s.', ResetServices::class, ServicesResetter::class));
        }

        return new ResetServices($arguments[0]);
    }
}
