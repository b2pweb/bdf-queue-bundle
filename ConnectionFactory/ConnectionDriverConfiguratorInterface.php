<?php

namespace Bdf\QueueBundle\ConnectionFactory;

use Bdf\Queue\Connection\ConnectionDriverInterface;
use Bdf\Queue\Serializer\SerializerInterface;

/**
 * Sub factory of connection driver. Create your own factory with this interface to create your own connection driver.
 */
interface ConnectionDriverConfiguratorInterface
{
    /**
     * Returns all the supported drivers
     *
     * @return string[]
     */
    public function getSupportedDrivers(): array;

    /**
     * Configure the custom connection driver.
     */
    public function configure(Configuration $config, SerializerInterface $serializer): ConnectionDriverInterface;
}