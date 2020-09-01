<?php

namespace Bdf\QueueBundle\Consumption;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverBuilder;
use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\Receiver\Builder\ReceiverLoaderInterface;
use Psr\Container\ContainerInterface;

/**
 * Loader for receiver configuration
 */
class ReceiverLoader implements ReceiverLoaderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ReceiverFactory
     */
    private $factory;

    /**
     * @var array[]
     */
    private $configuration;


    /**
     * ReceiverLoader constructor.
     *
     * @param ContainerInterface $container
     * @param array[] $configuration
     * @param ReceiverFactory|null $factory
     */
    public function __construct(ContainerInterface $container, array $configuration, ReceiverFactory $factory = null)
    {
        $this->container = $container;
        $this->configuration = $configuration;
        $this->factory = $factory;
    }

    /**
     * Load the receiver build from the configuration
     *
     * @param string $name The destination name
     *
     * @return ReceiverBuilder
     */
    public function load(string $name): ReceiverBuilder
    {
        $builder = new ReceiverBuilder($this->container, null, $this->factory);

        if (!isset($this->configuration[$name])) {
            return $builder;
        }

        $this->configure($builder, $this->configuration[$name]);

        return $builder;
    }

    /**
     * @param ReceiverBuilder $builder
     *
     * @param array $config
     */
    private function configure(ReceiverBuilder $builder, array $config)
    {
        if ($config['middlewares'] ?? false) {
            foreach ($config['middlewares'] as $middleware => $parameters) {
                if (is_int($middleware)) {
                    $middleware = $parameters;
                    $parameters = [];
                }

                $builder->add($middleware, $parameters);
            }
        }

        if ($config['retry'] ?? false) {
            $builder->retry($config['retry']);
        }

        if ($config['save'] ?? false) {
            $builder->store();
        }

        if ($config['limit'] ?? false) {
            $builder->limit($config['limit']);
        }

        if (($config['no_failure'] ?? false)) {
            $builder->noFailure();
        }

        if ($config['stop_when_empty'] ?? false) {
            $builder->stopWhenEmpty();
        }

        if ($config['max'] ?? false) {
            $builder->max($config['max']);
        }

        if ($config['memory'] ?? false) {
            $builder->memory($this->convertToBytes($config['memory']));
        }

        if ($config['handler'] ?? false) {
            $builder->handler($config['handler']);
        }

        if ($config['auto_handle'] ?? false) {
            $builder->jobProcessor();
        }
    }

    /**
     * Convert the given string value in bytes.
     *
     * @param string $value
     *
     * @return int
     */
    public function convertToBytes(string $value): int
    {
        $value = strtolower(trim($value));
        $unit = substr($value, -1);
        $bytes = (int) $value;

        switch ($unit) {
            case 't': $bytes *= 1024;
            // no break
            case 'g': $bytes *= 1024;
            // no break
            case 'm': $bytes *= 1024;
            // no break
            case 'k': $bytes *= 1024;
        }

        return $bytes;
    }
}
