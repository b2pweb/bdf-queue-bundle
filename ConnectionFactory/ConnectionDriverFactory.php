<?php

namespace Bdf\QueueBundle\ConnectionFactory;

use Bdf\Queue\Connection\AmqpLib\AmqpLibConnection;
use Bdf\Queue\Connection\ConnectionDriverInterface;
use Bdf\Queue\Connection\Factory\ConnectionDriverFactoryInterface;
use Bdf\Queue\Connection\Gearman\GearmanConnection;
use Bdf\Queue\Connection\Memory\MemoryConnection;
use Bdf\Queue\Connection\Null\NullConnection;
use Bdf\Queue\Connection\Pheanstalk\PheanstalkConnection;
use Bdf\Queue\Connection\RdKafka\RdKafkaConnection;
use Bdf\Queue\Connection\Redis\RedisConnection;
use Bdf\Queue\Serializer\SerializerInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 *
 */
final class ConnectionDriverFactory implements ConnectionDriverFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $containerId;

    /**
     * @var string
     */
    private $defaultConnection;


    /**
     * @param ContainerInterface $container
     * @param string $defaultConnection The default connection name
     * @param string $containerId
     */
    public function __construct(ContainerInterface $container, string $defaultConnection = null, string $containerId = 'bdf_queue.connection_definition.%s')
    {
        $this->container = $container;
        $this->containerId = $containerId;
        $this->defaultConnection = $defaultConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function create(?string $name): ConnectionDriverInterface
    {
        $id = sprintf($this->containerId, $name);

        if (!$this->container->has($id)) {
            throw new InvalidArgumentException('No queue driver has been set for '.$name);
        }

        return $this->container->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function defaultConnectionName(): string
    {
        return $this->defaultConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultConnection(): ConnectionDriverInterface
    {
        return $this->create($this->defaultConnection);
    }

    /**
     * Create the connection driver instance
     *
     * @param string $driver
     * @param array $config
     * @param SerializerInterface $serializer
     *
     * @return ConnectionDriverInterface
     */
    public function createDriver(string $driver, array $config, SerializerInterface $serializer)
    {
        switch ($driver) {
            case 'null':
                $connection = new NullConnection($config['connection']);
                $connection->setConfig($config);
                return $connection;

            case 'memory':
                $connection = new MemoryConnection($config['connection'], $serializer);
                $connection->setConfig($config);
                return $connection;

            case 'gearman':
                $connection = new GearmanConnection($config['connection'], $serializer);
                $connection->setConfig($config);
                return $connection;

            case 'amqp-lib':
                $connection = new AmqpLibConnection(
                    $config['connection'],
                    $serializer,
                    isset($config['exchange_resolver']) ? $this->container->get($config['exchange_resolver']) : null
                );
                $connection->setConfig($config);
                return $connection;

            case 'pheanstalk':
                $connection = new PheanstalkConnection($config['connection'], $serializer);
                $connection->setConfig($config);
                return $connection;

            case 'rdkafka':
                $connection = new RdKafkaConnection($config['connection'], $serializer);
                $connection->setConfig($config);
                return $connection;

            case 'redis':
                $connection = new RedisConnection($config['connection'], $serializer);
                $connection->setConfig($config);
                return $connection;

        }

        throw new InvalidArgumentException('The queue driver "'.$driver.'" does not exist. Did you forget to add "connection_factory" option ?');
    }
}
