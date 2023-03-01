<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

use Bdf\Queue\Consumer\DelegateHelper;
use Bdf\Queue\Consumer\ReceiverInterface;

class FooReceiver implements ReceiverInterface
{
    use DelegateHelper;

    public function __construct(public array $parameters)
    {
    }
}
