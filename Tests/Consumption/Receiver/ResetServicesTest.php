<?php

namespace Bdf\QueueBundle\Tests\Consumption\Receiver;

use Bdf\Queue\Consumer\ConsumerInterface;
use Bdf\Queue\Consumer\ReceiverInterface;
use Bdf\QueueBundle\Consumption\Receiver\ResetServices;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

/**
 *
 */
class ResetServicesTest extends TestCase
{
    /**
     *
     */
    public function test_receive_reset_services()
    {
        $message = new \stdClass();
        $consumer = $this->createMock(ConsumerInterface::class);

        $resetter = $this->createMock(ServicesResetter::class);
        $resetter->expects($this->once())->method('reset');

        $next = $this->createMock(ReceiverInterface::class);
        $next->expects($this->once())->method('receive')->with($message, $consumer);

        $receiver = new ResetServices($next, $resetter);
        $receiver->receive($message, $consumer);
    }

}
