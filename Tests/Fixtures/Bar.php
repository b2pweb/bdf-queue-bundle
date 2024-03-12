<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

class Bar
{
    /**
     * @var int
     */
    public $value;

    /**
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }
}
