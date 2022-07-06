<?php

namespace Bdf\QueueBundle\ConnectionFactory;

use Bdf\Queue\Connection\ConnectionDriverInterface;
use Bdf\Queue\Connection\Prime\PrimeConnection;
use Bdf\Queue\Serializer\SerializerInterface;
use Psr\Container\ContainerInterface;

/**
 * Create a prime connection from "b2pweb/bdf-queue-prime-adapter".
 *
 * Use "prime" key as driver to create a prime connection.
 * ex:
 *  `prime://{prime_connection_name}?table={db_table}`
 */
class PrimeConnectionFactory implements ConnectionDriverConfiguratorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedDrivers(): array
    {
        return ['prime'];
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Configuration $config, SerializerInterface $serializer): ConnectionDriverInterface
    {
        if (!class_exists(PrimeConnection::class)) {
            throw new \LogicException('Could not create the prime connection bdf-queue. Try to composer require "b2pweb/bdf-queue-prime-adapter" to add this class.');
        }

        $connection = new PrimeConnection($config->getConnection(), $serializer, $this->container->get('prime')->connections());
        $connection->setConfig($config->toArray());

        return $connection;
    }
}
