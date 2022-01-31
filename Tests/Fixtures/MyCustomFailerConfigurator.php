<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

use Bdf\Dsn\DsnRequest;
use Bdf\Queue\Failer\MemoryFailedJobRepository;
use Bdf\QueueBundle\DependencyInjection\Failer\FailerDriverConfiguratorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MyCustomFailerConfigurator implements FailerDriverConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function scheme(): string
    {
        return 'custom';
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
        $container->register('foo')
            ->setClass(MemoryFailedJobRepository::class)
            ->setPublic(true)
        ;

        return 'foo';
    }
}
