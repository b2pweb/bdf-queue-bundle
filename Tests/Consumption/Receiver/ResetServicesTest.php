<?php

namespace Bdf\QueueBundle\Tests\Consumption\Receiver;

use Bdf\Queue\Consumer\Receiver\NextInterface;
use Bdf\QueueBundle\Consumption\Receiver\ResetServices;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

class ResetServicesTest extends TestCase
{
    public function testReceiveResetServices()
    {
        $message = new \stdClass();

        $resetter = $this->createMock(ServicesResetter::class);
        $resetter->expects($this->once())->method('reset');

        $next = $this->createMock(NextInterface::class);
        $next->expects($this->once())->method('receive')->with($message, $next);

        $receiver = new ResetServices($resetter);
        $receiver->receive($message, $next);
    }
}
