<?php

namespace Bdf\QueueBundle\Tests\FailerFactory;

use Bdf\Queue\Failer\MemoryFailedJobRepository;
use Bdf\QueueBundle\FailerFactory\FailerFactory;
use Bdf\QueueBundle\FailerFactory\MemoryFailerFactory;
use PHPUnit\Framework\TestCase;

class FailerFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function test_create_success()
    {
        $factory = new FailerFactory();
        $factory->register(new MemoryFailerFactory());

        $this->assertInstanceOf(MemoryFailedJobRepository::class, $factory->createFromDsn('memory:'));
    }

    /**
     * @return void
     */
    public function test_create_invalid_scheme()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported failer scheme "invalid"');

        $factory = new FailerFactory();
        $factory->register(new MemoryFailerFactory());

        $factory->createFromDsn('invalid:');
    }
}
