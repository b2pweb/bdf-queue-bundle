<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\ReceiverInterface;
use Bdf\QueueBundle\Consumption\ReceiverFactoryInterface;

class FooReceiverFactory implements ReceiverFactoryInterface
{
    public function getReceiverNames(): array
    {
        return ['foo'];
    }

    public function create(ReceiverFactory $factory, ...$arguments): ReceiverInterface
    {
        return new FooReceiver($arguments);
    }
}
