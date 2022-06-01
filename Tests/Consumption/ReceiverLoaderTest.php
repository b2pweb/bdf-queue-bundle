<?php

namespace Bdf\QueueBundle\Tests\Consumption;

use Bdf\Instantiator\Instantiator;
use Bdf\Instantiator\InstantiatorInterface;
use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\Receiver\MemoryLimiterReceiver;
use Bdf\Queue\Consumer\Receiver\MessageCountLimiterReceiver;
use Bdf\Queue\Consumer\Receiver\MessageLoggerReceiver;
use Bdf\Queue\Consumer\Receiver\NoFailureReceiver;
use Bdf\Queue\Consumer\Receiver\ProcessorReceiver;
use Bdf\Queue\Consumer\Receiver\RateLimiterReceiver;
use Bdf\Queue\Consumer\Receiver\RetryMessageReceiver;
use Bdf\QueueBundle\Consumption\Receiver\ResetServices;
use Bdf\QueueBundle\Consumption\Receiver\ResetServicesFactory;
use Bdf\QueueBundle\Consumption\ReceiverLoader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

/**
 *
 */
class ReceiverLoaderTest extends TestCase
{
    private $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->set(InstantiatorInterface::class, new Instantiator($this->container));
    }

    private function getLoader(array $configuration = []): ReceiverLoader
    {
        $resetFactory = new ResetServicesFactory();
        $factory = new ReceiverFactory($this->container);
        $factory->addFactory($resetFactory->getReceiverNames(), [$resetFactory, 'create']);

        return new ReceiverLoader($this->container, $configuration, $factory);
    }

    /**
     *
     */
    public function test_unknown_destination_config()
    {
        $loader = $this->getLoader();
        $builder = $loader->load('foo');

        $chain = MessageLoggerReceiver::class.'->'.ProcessorReceiver::class;

        $this->assertEquals($chain, (string) $builder->build());
    }

    /**
     *
     */
    public function test_full_config()
    {
        $loader = $this->getLoader([
            'foo' => [
                'retry' => '1',
                'limit' => '2',
                'no_failure' => true,
                'stop_when_empty' => '',
                'max' => '1',
                'memory' => '128',
                'no_reset' => true,
            ]
        ]);
        $builder = $loader->load('foo');

        $chain = MemoryLimiterReceiver::class.'->'.MessageCountLimiterReceiver::class.'->'.NoFailureReceiver::class.'->'
            .RateLimiterReceiver::class.'->'.RetryMessageReceiver::class.'->'.MessageLoggerReceiver::class.'->'.ProcessorReceiver::class;

        $this->assertEquals($chain, (string) $builder->build());
    }

    /**
     *
     */
    public function test_without_service_resetter_config()
    {
        $loader = $this->getLoader([
            'foo' => [
                'retry' => '1',
                'limit' => '2',
                'no_failure' => true,
                'stop_when_empty' => '',
                'max' => '1',
                'memory' => '128',
            ]
        ]);
        $builder = $loader->load('foo');

        $chain = MemoryLimiterReceiver::class.'->'.MessageCountLimiterReceiver::class.'->'.NoFailureReceiver::class.'->'
            .RateLimiterReceiver::class.'->'.RetryMessageReceiver::class.'->'.MessageLoggerReceiver::class.'->'.ProcessorReceiver::class;

        $this->assertEquals($chain, (string) $builder->build());
    }

    /**
     *
     */
    public function test_default_config()
    {
        $this->container->set('services_resetter', $this->createMock(ServicesResetter::class));

        $loader = $this->getLoader([
            'foo' => [
            ]
        ]);
        $builder = $loader->load('foo');

        $chain = ResetServices::class.'->'.MessageLoggerReceiver::class.'->'.ProcessorReceiver::class;

        $this->assertEquals($chain, (string)$builder->build());
    }

    /**
     *
     */
    public function test_full_order_config()
    {
        $this->container->set('services_resetter', $this->createMock(ServicesResetter::class));

        $loader = $this->getLoader([
            'foo' => [
                'retry' => '1',
                'limit' => '2',
                'no_failure' => true,
                'stop_when_empty' => '',
                'max' => '1',
                'memory' => '128',
            ]
        ]);
        $builder = $loader->load('foo');

        $chain = ResetServices::class.'->'.MemoryLimiterReceiver::class.'->'.MessageCountLimiterReceiver::class.'->'.NoFailureReceiver::class.'->'
            .RateLimiterReceiver::class.'->'.RetryMessageReceiver::class.'->'.MessageLoggerReceiver::class.'->'.ProcessorReceiver::class;

        $this->assertEquals($chain, (string)$builder->build());
    }
}


class Container implements ContainerInterface
{
    private $service;

    public function __construct(array $service = [])
    {
        $this->service = $service;
    }

    public function set(string $id, $service): void
    {
        $this->service[$id] = $service;
    }

    public function get(string $id)
    {
        return $this->service[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->service[$id]);
    }
}