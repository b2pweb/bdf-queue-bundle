<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

use Bdf\Queue\Connection\ConnectionDriverInterface;
use Bdf\Queue\Connection\Factory\ConnectionDriverFactoryInterface;

class GetConnection
{
    private ConnectionDriverFactoryInterface $factory;

    public function __construct(ConnectionDriverFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function get(string $name): ConnectionDriverInterface
    {
        return $this->factory->create($name);
    }
}
