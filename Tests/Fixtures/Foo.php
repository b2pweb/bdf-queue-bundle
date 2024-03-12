<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

class Foo
{
    /**
     * @var string
     */
    public $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
