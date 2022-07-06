<?php

namespace Bdf\QueueBundle\DependencyInjection\Failer;

use Bdf\Dsn\DsnRequest;
use Bdf\Queue\Failer\MemoryFailedJobRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configurator for `MemoryFailedJobRepository`
 * Does not take parameters.
 *
 * DSN format: "memory:"
 */
final class MemoryFailerDriverConfigurator implements FailerDriverConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function scheme(): string
    {
        return 'memory';
    }

    /**
     * {@inheritdoc}
     */
    public function available(ContainerBuilder $container): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DsnRequest $dsn, ContainerBuilder $container): string
    {
        $container->register(MemoryFailedJobRepository::class, MemoryFailedJobRepository::class);

        return MemoryFailedJobRepository::class;
    }
}
