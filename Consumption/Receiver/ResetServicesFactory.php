<?php

namespace Bdf\QueueBundle\Consumption\Receiver;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\ReceiverInterface;
use Bdf\QueueBundle\Consumption\ReceiverFactoryProviderInterface;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

class ResetServicesFactory implements ReceiverFactoryProviderInterface
{
    public function getReceiverNames(): array
    {
        return ['reset'];
    }

    public function create(ReceiverFactory $factory, ReceiverInterface $receiver, ...$arguments): ReceiverInterface
    {
        if (!isset($arguments[0]) || !($arguments[0] instanceof ServicesResetter)) {
            throw new \LogicException(
                sprintf('First argument of %s should be an instance of %s.', ResetServices::class, ServicesResetter::class)
            );
        }

        return new ResetServices($receiver, $arguments[0]);
    }
}
