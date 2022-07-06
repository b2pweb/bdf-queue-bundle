<?php

namespace Bdf\QueueBundle\Tests;

require_once __DIR__.'/TestKernel.php';

use Bdf\Queue\Connection\Prime\PrimeConnection;
use Bdf\Queue\Console\Command\BindCommand;
use Bdf\Queue\Console\Command\ConsumeCommand;
use Bdf\Queue\Console\Command\Failer\AbstractFailerCommand;
use Bdf\Queue\Console\Command\Failer\DeleteCommand;
use Bdf\Queue\Console\Command\Failer\FlushCommand;
use Bdf\Queue\Console\Command\Failer\ForgetCommand;
use Bdf\Queue\Console\Command\Failer\RetryCommand;
use Bdf\Queue\Console\Command\Failer\ShowCommand;
use Bdf\Queue\Console\Command\InfoCommand;
use Bdf\Queue\Console\Command\ProduceCommand;
use Bdf\Queue\Console\Command\SetupCommand;
use Bdf\Queue\Consumer\Receiver\Builder\ReceiverLoaderInterface;
use Bdf\Queue\Destination\DestinationInterface;
use Bdf\Queue\Destination\DestinationManager;
use Bdf\Queue\Failer\FailedJobStorageInterface;
use Bdf\Queue\Failer\MemoryFailedJobRepository;
use Bdf\Queue\Message\Message;
use Bdf\Queue\Message\QueuedMessage;
use Bdf\Queue\Testing\QueueHelper;
use Bdf\QueueBundle\BdfQueueBundle;
use Bdf\QueueBundle\Consumption\ReceiverLoader;
use Bdf\QueueBundle\Tests\Fixtures\TestHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * BdfSerializerBundleTest.
 */
class BdfQueueBundleTest extends TestCase
{
    public function testDefaultConfig()
    {
        $builder = $this->createMock(ContainerBuilder::class);

        $bundle = new BdfQueueBundle();

        $this->assertNull($bundle->build($builder));
    }

    public function testKernel()
    {
        $kernel = new \TestKernel();
        $kernel->boot();

        $this->assertInstanceOf(DestinationManager::class, $kernel->getContainer()->get('bdf_queue.destination_manager'));
        $this->assertSame($kernel->getContainer()->get('bdf_queue.destination_manager'), $kernel->getContainer()->get(DestinationManager::class));
        $this->assertInstanceOf(ReceiverLoader::class, $kernel->getContainer()->get('bdf_queue.receiver.loader'));
        $this->assertInstanceOf(ReceiverLoader::class, $kernel->getContainer()->get(ReceiverLoaderInterface::class));
    }

    public function testFunctional()
    {
        $kernel = new \TestKernel(__DIR__.'/Fixtures/conf_functional.yaml');
        $kernel->boot();

        /** @var DestinationManager $destinations */
        $destinations = $kernel->getContainer()->get('bdf_queue.destination_manager');
        $destination = $destinations->create('test');

        $this->assertInstanceOf(DestinationInterface::class, $destination);

        /** @var TestHandler $handler */
        $handler = $kernel->getContainer()->get(TestHandler::class);

        $helper = new QueueHelper($kernel->getContainer());
        $destination->send(new Message(['foo' => 'bar']));

        $helper->consume(1, 'test');
        $this->assertEquals([['foo' => 'bar']], $handler->messages);
    }

    public function testWithJsonSerializer()
    {
        $kernel = new \TestKernel(__DIR__.'/Fixtures/conf_with_json_serializer.yaml');
        $kernel->boot();

        /** @var DestinationManager $destinations */
        $destinations = $kernel->getContainer()->get('bdf_queue.destination_manager');
        $destination = $destinations->create('test');

        $this->assertInstanceOf(DestinationInterface::class, $destination);

        /** @var TestHandler $handler */
        $handler = $kernel->getContainer()->get(TestHandler::class);

        $helper = new QueueHelper($kernel->getContainer());
        $destination->send(new Message(new class() implements \JsonSerializable {
            public function jsonSerialize()
            {
                return ['foo' => 'bar'];
            }
        }));

        /** @var QueuedMessage $message */
        $message = $helper->peek(1, 'test')[0];

        $this->assertEquals(
            '{"data":{"foo":"bar"},"queuedAt":'.json_encode($message->queuedAt()).'}',
            $message->raw()
        );
    }

    public function testConnectionOptions()
    {
        $kernel = new \TestKernel();
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
            ],
        ], $configs);
    }

    public function testDestinationOptions()
    {
        $kernel = new \TestKernel();
        $kernel->boot();

        $configs = $kernel->getContainer()->getParameter('bdf_queue.destinations');

        $this->assertEquals([
            'b2p_bus' => 'queue://gearman/b2p_bus',
            'custom_bus' => 'queue://gearman/custom_bus',
        ], $configs);
    }

    public function testCommands()
    {
        $kernel = new \TestKernel();
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

    /**
     * @return void
     */
    public function testFailer()
    {
        $kernel = new \TestKernel();
        $kernel->boot();
        $console = new Application($kernel);

        $command = $console->get('queue:failer:delete');

        $r = new \ReflectionProperty(AbstractFailerCommand::class, 'repository');
        $r->setAccessible(true);

        /** @var FailedJobStorageInterface $failer */
        $failer = $r->getValue($command);

        $this->assertInstanceOf(MemoryFailedJobRepository::class, $failer);
    }

    /**
     * @return void
     */
    public function testCustomFailer()
    {
        $kernel = new \TestKernel(__DIR__.'/Fixtures/conf_with_custom_failer.yaml');
        $kernel->boot();
        $console = new Application($kernel);

        $command = $console->get('queue:failer:delete');

        $r = new \ReflectionProperty(AbstractFailerCommand::class, 'repository');
        $r->setAccessible(true);

        /** @var FailedJobStorageInterface $failer */
        $failer = $r->getValue($command);

        $this->assertInstanceOf(MemoryFailedJobRepository::class, $failer);
        $this->assertSame($failer, $kernel->getContainer()->get('foo'));
    }

    public function testPrimeConnection()
    {
        if (class_exists(PrimeConnection::class)) {
            $this->markTestSkipped('b2pweb/bdf-queue-prime-adapter installed');
        }

        $this->expectException(\LogicException::class);

        $kernel = new \TestKernel(__DIR__.'/Fixtures/conf_with_prime_connection.yaml');
        $kernel->boot();

        /** @var DestinationManager $destinations */
        $destinations = $kernel->getContainer()->get('bdf_queue.destination_manager');
        $destinations->queue('prime', 'queue_name');
    }
}
