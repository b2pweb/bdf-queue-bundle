<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\ReceiverInterface;
use Bdf\QueueBundle\Consumption\ReceiverFactoryProviderInterface;

class LegacyReceiverFactory implements ReceiverFactoryProviderInterface
{
    public function getReceiverNames(): array
    {
        return ['legacy'];
    }

    public function create(ReceiverFactory $factory, ReceiverInterface $receiver, ...$arguments): ReceiverInterface
    {
        return new FooReceiver($arguments);
    }
}
