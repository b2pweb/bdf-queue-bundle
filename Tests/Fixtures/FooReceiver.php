<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

use Bdf\Queue\Consumer\DelegateHelper;
use Bdf\Queue\Consumer\ReceiverInterface;

class FooReceiver implements ReceiverInterface
{
    use DelegateHelper;

    public $parameters;

    public function __construct(ReceiverInterface $delegate, array $parameters)
    {
        $this->delegate = $delegate;
        $this->parameters = $parameters;
    }
}