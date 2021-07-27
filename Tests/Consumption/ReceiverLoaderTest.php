<?php

namespace Bdf\QueueBundle\Tests\Consumption;

use Bdf\Instantiator\Instantiator;
use Bdf\Instantiator\InstantiatorInterface;
use Bdf\Queue\Consumer\Receiver\Builder\ReceiverBuilder;
use Bdf\Queue\Consumer\Receiver\MemoryLimiterReceiver;
use Bdf\Queue\Consumer\Receiver\MessageCountLimiterReceiver;
use Bdf\Queue\Consumer\Receiver\MessageLoggerReceiver;
use Bdf\Queue\Consumer\Receiver\NoFailureReceiver;
use Bdf\Queue\Consumer\Receiver\ProcessorReceiver;
use Bdf\Queue\Consumer\Receiver\RateLimiterReceiver;
use Bdf\Queue\Consumer\Receiver\RetryMessageReceiver;
use Bdf\QueueBundle\Consumption\ReceiverLoader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 *
 */
class ReceiverLoaderTest extends TestCase
{
    /**
     *
     */
    public function test_default_build()
    {
        $container = $this->createMock(ContainerInterface::class);

        $loader = new ReceiverLoader($container, []);
        $builder = $loader->load('foo');

        $this->assertEquals(new ReceiverBuilder($container), $builder);
    }

    /**
     *
     */
    public function test_config()
    {
        $container = new Container();
        $container->set(InstantiatorInterface::class, new Instantiator($container));

        $loader = new ReceiverLoader($container, [
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

        $this->assertEquals($chain, (string)$builder->build());
    }
}


class Container implements \Psr\Container\ContainerInterface
{
    private $service;

    public function __construct(array $service = [])
    {
        $this->service = $service;
    }

    /**
     * @param string $id
     * @param mixed $service
     */
    public function set($id, $service): void
    {
        $this->service[$id] = $service;
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->service[$id];
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return isset($this->service[$id]);
    }
}