<?php

namespace Bdf\QueueBundle\DependencyInjection;

use Bdf\QueueBundle\ConnectionFactory\ConnectionDriverFactory;
use Bdf\Queue\Connection\ConnectionDriverInterface;
use Bdf\Queue\Connection\Factory\ResolverConnectionDriverFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * QueueExtension
 */
class BdfQueueExtension extends Extension
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('queue.yaml');

        $this->configureConnections($config, $container);
        $this->configureDestinations($config, $container);
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    public function configureConnections(array $config, ContainerBuilder $container): void
    {
        // pre format driver options
        $preparedConfigs = [];
        foreach ($config['connections'] as $name => $options) {
            if (isset($options['url'])) {
                $preparedConfigs[$name] = ResolverConnectionDriverFactory::parseDsn($options['url']);
            }

            if (!empty($options['options'])) {
                $options = array_merge($options, $options['options']);
            }

            unset($options['url'], $options['options'], $options['connection_factory']);

            $preparedConfigs[$name] = array_merge($preparedConfigs[$name], array_filter($options));
        }

        // create connection definition
        $connectionConfigs = [];
        $factory = new ResolverConnectionDriverFactory($preparedConfigs, $config['default_connection']);

        foreach ($config['connections'] as $name => $options) {
            $connectionConfig = $factory->getConfig($name);
            $connectionConfigs[$name] = $connectionConfig;

            $container->register('bdf_queue.connection_definition.'.$name, ConnectionDriverInterface::class)
                ->setPublic(true)
                ->setFactory($options['connection_factory'] ?? [new Reference(ConnectionDriverFactory::class), 'createDriver'])
                ->setArguments([
                    $connectionConfig['driver'],
                    $connectionConfig,
                    new Reference($this->configureSerializer($options['serializer'], $config['default_serializer']))
                ]);
        }

        $container->setParameter('bdf_queue.default_connection', $config['default_connection'] ?? key($connectionConfigs));
        $container->setParameter('bdf_queue.connections', $connectionConfigs);
    }

    /**
     * @param array $config
     * @param string $default
     *
     * @return string
     */
    private function configureSerializer(array $config, string $default): string
    {
        if (isset($config['service'])) {
            return $config['service'];
        }

        return 'bdf_queue.serializer.'.($config['id'] ?? $default);
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    public function configureDestinations(array $config, ContainerBuilder $container): void
    {
        $destinations = [];
        $consumptionConfig = [];

        foreach ($config['destinations'] as $name => $options) {
            $destinations[$name] = $options['url'];

            // TODO build a builder rule in the container for this destination
            foreach ($options['consumer'] as $option => $value) {
                if ($value !== null) {
                    if (($value[0] ?? '') === '@') {
                        $value = new Reference(ltrim($value, '@'));
                    }

                    $consumptionConfig[$name][$option] = $value;
                }
            }
        }

        $container->getDefinition('bdf_queue.receiver.loader')
            ->replaceArgument(1, $consumptionConfig);

        $container->setParameter('bdf_queue.destinations', $destinations);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }
}
