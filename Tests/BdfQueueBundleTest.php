<?php

namespace Bdf\QueueBundle\Tests;

require_once __DIR__.'/TestKernel.php';

use Bdf\Queue\Console\Command\BindCommand;
use Bdf\Queue\Console\Command\ConsumeCommand;
use Bdf\Queue\Console\Command\Failer\DeleteCommand;
use Bdf\Queue\Console\Command\Failer\FlushCommand;
use Bdf\Queue\Console\Command\Failer\ForgetCommand;
use Bdf\Queue\Console\Command\Failer\RetryCommand;
use Bdf\Queue\Console\Command\Failer\ShowCommand;
use Bdf\Queue\Console\Command\InfoCommand;
use Bdf\Queue\Console\Command\ProduceCommand;
use Bdf\Queue\Console\Command\SetupCommand;
use Bdf\Queue\Destination\DestinationInterface;
use Bdf\Queue\Destination\DestinationManager;
use Bdf\QueueBundle\BdfQueueBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
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
                'driver' => null,
                'host' => null,
                'serializer' => ['id' => 'native'],
                'queue' => null,
                'url' => 'gearman://127.0.0.1',
                'options' => ['client_timeout' => 1],
                'vendor' => null,
                'port' => null,
                'user' => null,
                'password' => null,
                'connection_factory' => null,
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

    /**
     *
     */
    public function test_commands()
    {
        $kernel = new \TestKernel('dev', true);
        $kernel->boot();
        $console = new Application($kernel);

        $this->assertInstanceOf(BindCommand::class, $console->get('queue:bind'));
        $this->assertInstanceOf(ConsumeCommand::class, $console->get('queue:consume'));
        $this->assertInstanceOf(InfoCommand::class, $console->get('queue:info'));
        $this->assertInstanceOf(ProduceCommand::class, $console->get('queue:produce'));
        $this->assertInstanceOf(SetupCommand::class, $console->get('queue:setup'));

        $this->assertInstanceOf(DeleteCommand::class, $console->get('queue:failer:delete'));
        $this->assertInstanceOf(RetryCommand::class, $console->get('queue:failer:retry'));
        $this->assertInstanceOf(ShowCommand::class, $console->get('queue:failer:show'));
        $this->assertInstanceOf(FlushCommand::class, $console->get('queue:failer:flush'));
        $this->assertInstanceOf(ForgetCommand::class, $console->get('queue:failer:forget'));
    }
}
