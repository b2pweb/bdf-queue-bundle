<?php

namespace Bdf\QueueBundle\Tests;

require_once __DIR__.'/TestKernel.php';

use Bdf\Queue\Connection\ConnectionDriverInterface;
use Bdf\Queue\Connection\Prime\PrimeConnection;
use Bdf\Queue\Console\Command\BindCommand;
use Bdf\Queue\Console\Command\ConsumeCommand;
use Bdf\Queue\Console\Command\Failer\AbstractFailerCommand;
use Bdf\Queue\Console\Command\Failer\DeleteCommand;
use Bdf\Queue\Console\Command\Failer\RetryCommand;
use Bdf\Queue\Console\Command\Failer\ShowCommand;
use Bdf\Queue\Console\Command\InfoCommand;
use Bdf\Queue\Console\Command\ProduceCommand;
use Bdf\Queue\Console\Command\SetupCommand;
use Bdf\Queue\Consumer\Receiver\Builder\ReceiverLoaderInterface;
use Bdf\Queue\Destination\CachedDestinationFactory;
use Bdf\Queue\Destination\ConfigurationDestinationFactory;
use Bdf\Queue\Destination\DestinationFactory;
use Bdf\Queue\Destination\DestinationInterface;
use Bdf\Queue\Destination\DestinationManager;
use Bdf\Queue\Destination\DsnDestinationFactory;
use Bdf\Queue\Failer\FailedJobStorageInterface;
use Bdf\Queue\Failer\MemoryFailedJobRepository;
use Bdf\Queue\Message\Message;
use Bdf\Queue\Message\QueuedMessage;
use Bdf\Queue\Serializer\JsonSerializer;
use Bdf\Queue\Serializer\Serializer;
use Bdf\Queue\Testing\QueueHelper;
use Bdf\QueueBundle\BdfQueueBundle;
use Bdf\QueueBundle\Consumption\ReceiverLoader;
use Bdf\QueueBundle\Tests\Fixtures\Bar;
use Bdf\QueueBundle\Tests\Fixtures\Foo;
use Bdf\QueueBundle\Tests\Fixtures\GetConnection;
use Bdf\QueueBundle\Tests\Fixtures\GetDestinationFactory;
use Bdf\QueueBundle\Tests\Fixtures\TestHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LazyCommand;
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

    public function testWithBind()
    {
        $kernel = new \TestKernel(__DIR__.'/Fixtures/conf_with_bind.yaml');
        $kernel->boot();

        /** @var DestinationManager $destinations */
        $destinations = $kernel->getContainer()->get('bdf_queue.destination_manager');
        $destination = $destinations->create('test');

        $this->assertInstanceOf(DestinationInterface::class, $destination);

        /** @var TestHandler $handler */
        $handler = $kernel->getContainer()->get(TestHandler::class);

        $helper = new QueueHelper($kernel->getContainer());
        $destination->send((new Message(new Foo('azerty')))->setName('Foo'));
        $destination->send((new Message(new Bar(42)))->setName('Bar'));
        $destination->send((new Message(new Bar(42)))->setName('Baz'));

        $helper->consume(3, 'test');
        $this->assertEquals([
            new Foo('azerty'),
            new Bar(42),
            ['value' => 42], // Not bound to a class
        ], $handler->messages);
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
        $destination->send(new Message(new class implements \JsonSerializable {
            #[\ReturnTypeWillChange]
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
                'serializer' => ['id' => 'native', 'from_url' => false],
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

        $this->assertInstanceOf(BindCommand::class, $this->getCommand($console, 'queue:bind'));
        $this->assertInstanceOf(ConsumeCommand::class, $this->getCommand($console, 'queue:consume'));
        $this->assertInstanceOf(InfoCommand::class, $this->getCommand($console, 'queue:info'));
        $this->assertInstanceOf(ProduceCommand::class, $this->getCommand($console, 'queue:produce'));
        $this->assertInstanceOf(SetupCommand::class, $this->getCommand($console, 'queue:setup'));

        $this->assertInstanceOf(DeleteCommand::class, $this->getCommand($console, 'queue:failer:delete'));
        $this->assertInstanceOf(RetryCommand::class, $this->getCommand($console, 'queue:failer:retry'));
        $this->assertInstanceOf(ShowCommand::class, $this->getCommand($console, 'queue:failer:show'));
    }

    /**
     * @return void
     */
    public function testFailer()
    {
        $kernel = new \TestKernel();
        $kernel->boot();
        $console = new Application($kernel);

        $command = $this->getCommand($console, 'queue:failer:delete');

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

        $command = $this->getCommand($console, 'queue:failer:delete');

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

    public function testWithSerializerFromUrl()
    {
        $kernel = new \TestKernel(__DIR__.'/Fixtures/conf_with_serializer_from_dsn.yaml');
        $kernel->boot();

        /** @var ConnectionDriverInterface $connection */
        $connection = $kernel->getContainer()->get(GetConnection::class)->get('queue');
        $this->assertInstanceOf(JsonSerializer::class, $connection->serializer());

        /** @var ConnectionDriverInterface $connection */
        $connection = $kernel->getContainer()->get(GetConnection::class)->get('no_defined');
        $this->assertInstanceOf(Serializer::class, $connection->serializer());
    }

    public function testDestinationFactoryInstance()
    {
        $kernel = new \TestKernel();
        $kernel->boot();

        /** @var GetDestinationFactory $service */
        $service = $kernel->getContainer()->get(GetDestinationFactory::class);

        if (class_exists(DestinationFactory::class)) {
            $this->assertInstanceOf(DestinationFactory::class, $service->destinationFactory);
        } else {
            $this->assertInstanceOf(CachedDestinationFactory::class, $service->destinationFactory);
        }

        $this->assertInstanceOf(DsnDestinationFactory::class, $service->legacyDsnDestinationFactory);
        $this->assertInstanceOf(ConfigurationDestinationFactory::class, $service->legacyConfigurationDestinationFactory);
        $this->assertEquals(['custom_bus', 'b2p_bus'], $service->legacyConfigurationDestinationFactory->destinationNames());
        $this->assertEquals(['custom_bus', 'b2p_bus'], $service->destinationFactory->destinationNames());
    }

    private function getCommand(Application $application, string $name): Command
    {
        $command = $application->get($name);

        return $command instanceof LazyCommand ? $command->getCommand() : $command;
    }
}
