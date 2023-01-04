<?php

namespace Bdf\QueueBundle\Consumption;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverBuilder;
use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\Receiver\Builder\ReceiverLoaderInterface;
use Psr\Container\ContainerInterface;

/**
 * Loader for receiver configuration.
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
     * @param array[] $configuration
     */
    public function __construct(ContainerInterface $container, array $configuration, ReceiverFactory $factory = null)
    {
        $this->container = $container;
        $this->configuration = $configuration;
        $this->factory = $factory;
    }

    /**
     * Load the receiver build from the configuration.
     *
     * @param string $name The destination name
     */
    public function load(string $name): ReceiverBuilder
    {
        $builder = new ReceiverBuilder($this->container, null, $this->factory);

        $this->configure($builder, $this->configuration[$name] ?? []);

        return $builder;
    }

    private function configure(ReceiverBuilder $builder, array $config)
    {
        $builder->log();

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

        if ($config['no_failure'] ?? false) {
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

        if (!($config['no_reset'] ?? false) && $this->container->has('services_resetter')) {
            $builder->add('reset', [$this->container->get('services_resetter')]);
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
