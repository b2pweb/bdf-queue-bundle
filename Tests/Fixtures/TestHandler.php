<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

class TestHandler
{
    public $messages = [];

    public function __invoke($message)
    {
        $this->messages[] = $message;
    }
}
