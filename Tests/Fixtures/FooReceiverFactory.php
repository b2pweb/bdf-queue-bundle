<?php

namespace Bdf\QueueBundle\Tests\Fixtures;


use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\ReceiverInterface;
use Bdf\QueueBundle\Consumption\ReceiverFactoryProviderInterface;

class FooReceiverFactory implements ReceiverFactoryProviderInterface
{

    public function getReceiverNames(): array
    {
        return ['foo'];
    }

    public function create(ReceiverFactory $factory, ReceiverInterface $receiver, ...$arguments): ReceiverInterface
    {
        return new FooReceiver($receiver, $arguments);
    }
}