<?php

namespace Bdf\QueueBundle\Tests\FailerFactory;

use Bdf\Dsn\Dsn;
use Bdf\Queue\Failer\MemoryFailedJobRepository;
use Bdf\QueueBundle\FailerFactory\MemoryFailerFactory;
use PHPUnit\Framework\TestCase;

class MemoryFailerFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function test()
    {
        $factory = new MemoryFailerFactory();

        $this->assertSame('memory', $factory->scheme());
        $this->assertInstanceOf(MemoryFailedJobRepository::class, $factory->create(Dsn::parse('memory:')));
    }
}
