<?php

namespace Bdf\QueueBundle\DependencyInjection;

use Bdf\Queue\Connection\ConnectionDriverInterface;
use Bdf\QueueBundle\ConnectionFactory\Configuration as DriverConfiguration;
use Bdf\QueueBundle\ConnectionFactory\ConnectionDriverConfiguratorInterface;
use Bdf\QueueBundle\ConnectionFactory\ConnectionDriverFactory;
use Bdf\QueueBundle\Consumption\ReceiverFactoryProviderInterface;
use Bdf\QueueBundle\Consumption\ReceiverLoader;
use Bdf\QueueBundle\DependencyInjection\Compiler\DriverFactoryPass;
use Bdf\QueueBundle\DependencyInjection\Compiler\RegisterFailerDriverPass;
use Bdf\QueueBundle\DependencyInjection\Compiler\RegisterReceiverFactoryPass;
use Bdf\QueueBundle\DependencyInjection\Failer\FailerDriverConfiguratorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * QueueExtension.
 */
class BdfQueueExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('queue.yaml');

        $this->configureConnections($config, $container);
        $this->configureDestinations($config, $container);
        $this->configureFailer($config, $container);
    }

    public function configureConnections(array $config, ContainerBuilder $container): void
    {
        $connectionConfigs = [];

        foreach ($config['connections'] as $name => $options) {
            $connectionConfigs[$name] = $options;

            $container->register('bdf_queue.config_definition.'.$name, DriverConfiguration::class)
                ->setFactory([DriverConfiguration::class, 'createConfiguration'])
                ->setArguments([$name, $options]);

            $container->register('bdf_queue.connection_definition.'.$name, ConnectionDriverInterface::class)
                ->setPublic(true)
                ->setFactory($options['connection_factory'] ?? [new Reference(ConnectionDriverFactory::class), 'createDriver'])
                ->setArguments([
                    new Reference('bdf_queue.config_definition.'.$name),
                    new Reference($this->configureSerializer($options['serializer'], $config['default_serializer'])),
                ]);
        }

        $container->registerForAutoconfiguration(ConnectionDriverConfiguratorInterface::class)
            ->setShared(false)
            ->setPublic(false)
            ->addTag(DriverFactoryPass::CONFIGURATOR_TAG_NAME);

        $container->setParameter('bdf_queue.default_connection', $config['default_connection'] ?? key($connectionConfigs));
        $container->setParameter('bdf_queue.connections', $connectionConfigs);
        $container->setParameter('bdf_queue.connection_names', array_keys($connectionConfigs));
    }

    private function configureSerializer(array $config, string $default): string
    {
        if (isset($config['service'])) {
            return $config['service'];
        }

        return 'bdf_queue.serializer.'.($config['id'] ?? $default);
    }

    public function configureDestinations(array $config, ContainerBuilder $container): void
    {
        $destinations = [];
        $consumptionConfig = [];

        foreach ($config['destinations'] as $name => $options) {
            $destinations[$name] = $options['url'];

            // TODO build a builder rule in the container for this destination
            foreach ($options['consumer'] ?? [] as $option => $value) {
                if (null !== $value) {
                    if (($value[0] ?? '') === '@') {
                        $value = new Reference(ltrim($value, '@'));
                    }

                    $consumptionConfig[$name][$option] = $value;
                }
            }
        }

        $container->registerForAutoconfiguration(ReceiverFactoryProviderInterface::class)
            ->setShared(false)
            ->setPublic(false)
            ->addTag(RegisterReceiverFactoryPass::CONFIGURATOR_TAG_NAME);

        $container->getDefinition(ReceiverLoader::class)
            ->replaceArgument(1, $consumptionConfig);

        $container->setParameter('bdf_queue.destinations', $destinations);
    }

    public function configureFailer(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('bdf_queue.failer_dsn', $config['failer']);

        $container->registerForAutoconfiguration(FailerDriverConfiguratorInterface::class)
            ->setShared(false)
            ->setPublic(false)
            ->addTag(RegisterFailerDriverPass::CONFIGURATOR_TAG_NAME)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }
}
