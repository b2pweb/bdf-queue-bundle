<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

class Bar
{
    /**
     * @var int
     */
    public $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }
}
