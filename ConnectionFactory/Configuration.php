<?php

namespace Bdf\QueueBundle\ConnectionFactory;

use Bdf\Queue\Connection\Factory\ResolverConnectionDriverFactory;

/**
 * Configuration.
 */
final class Configuration
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the driver name.
     */
    public function getDriver(): string
    {
        return $this->config['driver'];
    }

    /**
     * Get the connection name.
     */
    public function getConnection(): string
    {
        return $this->config['connection'];
    }

    /**
     * Get a configuration key.
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Check whether a configuration key exists.
     */
    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }

    /**
     * Get the array config.
     */
    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * Create the configuration of the connection driver.
     */
    public static function createConfiguration(string $name, array $options): self
    {
        // pre format driver options
        $preparedConfig = [];

        if (isset($options['url'])) {
            $preparedConfig = ResolverConnectionDriverFactory::parseDsn($options['url']);
        }
        if (!empty($options['options'])) {
            $options = array_merge($options, $options['options']);
        }

        unset($options['url'], $options['options'], $options['connection_factory']);
        $preparedConfig = array_merge($preparedConfig, array_filter($options));
        $factory = new ResolverConnectionDriverFactory([$name => $preparedConfig]);

        return new self($factory->getConfig($name));
    }
}
