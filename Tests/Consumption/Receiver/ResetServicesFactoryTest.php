<?php

namespace Bdf\QueueBundle\Tests\Consumption\Receiver;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\ReceiverInterface;
use Bdf\QueueBundle\Consumption\Receiver\ResetServices;
use Bdf\QueueBundle\Consumption\Receiver\ResetServicesFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

/**
 *
 */
class ResetServicesFactoryTest extends TestCase
{
    /**
     *
     */
    public function test_service_instantiation()
    {
        $receiverFactory = $this->createMock(ReceiverFactory::class);
        $resetter = $this->createMock(ServicesResetter::class);
        $next = $this->createMock(ReceiverInterface::class);

        $factory = new ResetServicesFactory();

        $this->assertInstanceOf(ResetServices::class, $factory->create($receiverFactory, $next, $resetter));
    }

    /**
     *
     */
    public function test_unknown_services_resetter()
    {
        $this->expectException(\LogicException::class);

        $receiverFactory = $this->createMock(ReceiverFactory::class);
        $next = $this->createMock(ReceiverInterface::class);

        $factory = new ResetServicesFactory();

        $this->assertInstanceOf(ResetServices::class, $factory->create($receiverFactory, $next));
    }
}
