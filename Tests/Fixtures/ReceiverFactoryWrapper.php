<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;

class ReceiverFactoryWrapper
{
    public $factory;

    public function __construct(ReceiverFactory $factory)
    {
        $this->factory = $factory;
    }
}