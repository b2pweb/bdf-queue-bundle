<?php

namespace Bdf\QueueBundle\Tests\Consumption;

require_once __DIR__.'/../TestKernel.php';

use Bdf\Queue\Consumer\ReceiverInterface;
use Bdf\QueueBundle\Tests\Fixtures\FooReceiver;
use Bdf\QueueBundle\Tests\Fixtures\ReceiverFactoryWrapper;
use PHPUnit\Framework\TestCase;

class RegisterReceiverFactoryPassTest extends TestCase
{
    public function testDefaultBuild()
    {
        $kernel = new \TestKernel(__DIR__.'/../Fixtures/conf_with_receiver_factory.yaml');
        $kernel->boot();

        $container = $kernel->getContainer();

        $delegate = $this->createMock(ReceiverInterface::class);

        /** @var ReceiverFactoryWrapper $wrapper */
        $wrapper = $container->get(ReceiverFactoryWrapper::class);
        $receiver = $wrapper->factory->create('foo', [$delegate, 'bar']);

        $this->assertInstanceOf(FooReceiver::class, $receiver);
        $this->assertSame(['bar'], $receiver->parameters);
    }
}
