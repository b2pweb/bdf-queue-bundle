<?php

namespace Bdf\QueueBundle\DependencyInjection\Failer;

use Bdf\Dsn\DsnRequest;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configure container for a given failer repository.
 */
interface FailerDriverConfiguratorInterface
{
    /**
     * The DSN scheme to use for the current failer repository.
     */
    public function scheme(): string;

    /**
     * Check if the current repository is available
     * This method should check for class and required service existence.
     *
     * @return bool true if the current repository can be used
     */
    public function available(ContainerBuilder $container): bool;

    /**
     * Configure the container for register the current failer repository.
     *
     * @param DsnRequest       $dsn       The parsed DSN
     * @param ContainerBuilder $container Container to configure
     *
     * @return string The service ID which contains the repository instance
     */
    public function configure(DsnRequest $dsn, ContainerBuilder $container): string;
}
