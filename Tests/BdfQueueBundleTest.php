<?php

namespace Bdf\QueueBundle\Tests;

require_once __DIR__.'/TestKernel.php';

use Bdf\Queue\Destination\DestinationInterface;
use Bdf\Queue\Destination\DestinationManager;
use Bdf\QueueBundle\BdfQueueBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * BdfSerializerBundleTest
 */
class BdfQueueBundleTest extends TestCase
{
    public function test_default_config()
    {
        $builder = $this->createMock(ContainerBuilder::class);

        $bundle = new BdfQueueBundle();

        $this->assertNull($bundle->build($builder));
    }

    /**
     *
     */
    public function test_kernel()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        $this->assertInstanceOf(DestinationManager::class, $kernel->getContainer()->get('bdf_queue.destination_manager'));
        $this->assertSame($kernel->getContainer()->get('bdf_queue.destination_manager'), $kernel->getContainer()->get(DestinationManager::class));
    }

    /**
     *
     */
    public function test_functional()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        /** @var DestinationManager $destinations */
        $destinations = $kernel->getContainer()->get('bdf_queue.destination_manager');
        $destination = $destinations->create('b2p_bus');

        $this->assertInstanceOf(DestinationInterface::class, $destination);
    }

    /**
     *
     */
    public function test_connection_options()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        $configs = $kernel->getContainer()->getParameter('bdf_queue.connections');

        $this->assertEquals([
            'gearman' => [
                'driver' => 'gearman',
                'host' => '127.0.0.1',
                'serializer' => ['id' => 'native'],
                'queue' => 'gearman',
                'connection' => 'gearman',
                'client_timeout' => 1,
            ]
        ], $configs);
    }

    /**
     *
     */
    public function test_destination_options()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();

        $configs = $kernel->getContainer()->getParameter('bdf_queue.destinations');

        $this->assertEquals([
            'b2p_bus' => 'queue://gearman/b2p_bus'
        ], $configs);
    }
}
