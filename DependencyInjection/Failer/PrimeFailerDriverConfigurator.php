<?php

namespace Bdf\QueueBundle\DependencyInjection\Failer;

use Bdf\Dsn\DsnRequest;
use Bdf\Queue\Console\Command\Failer\InitCommand;
use Bdf\Queue\Failer\DbFailedJobRepository;
use Bdf\Queue\Failer\DbFailedJobStorage;
use Bdf\Queue\Failer\FailedJobRepositoryAdapter;
use Bdf\Queue\Failer\FailedJobRepositoryInterface;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Configurator for failer repository using prime.
 *
 * The package "b2pweb/bdf-queue-prime-adapter" must be installed and
 * "b2pweb/bdf-prime-bundle" configure to enable this failer repository
 *
 * DSN format: "prime://[connection]/[table]?maxRows=[cursorSize]"
 * with:
 * - [connection] as the prime connection to use for store failed jobs
 * - [table] as the table name
 * - [cursorSize] (optional): number of loaded jobs each times by the cursor with calling
 *                `FailedJobRepositoryInterface::all()` or `search()`. Default value is 50
 */
final class PrimeFailerDriverConfigurator implements FailerDriverConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function scheme(): string
    {
        return 'prime';
    }

    /**
     * {@inheritdoc}
     */
    public function available(ContainerBuilder $container): bool
    {
        if (!$container->has('prime')) {
            return false;
        }

        return class_exists(DbFailedJobRepository::class) || class_exists(DbFailedJobStorage::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DsnRequest $dsn, ContainerBuilder $container): string
    {
        if (!$host = $dsn->getHost()) {
            throw new InvalidArgumentException('The connection name is required on prime failer DSN');
        }

        if (!$path = trim($dsn->getPath(), '/')) {
            throw new InvalidArgumentException('The table name is required on prime failer DSN');
        }

        $className = class_exists(DbFailedJobRepository::class)
            ? DbFailedJobRepository::class
            : DbFailedJobStorage::class
        ;

        $container->register($className, $className)
            ->setFactory([$className, 'make'])
            ->setArguments([
                new Reference('prime'),
                [
                    'connection' => $host,
                    'table' => $path,
                ],
                $dsn->query('maxRows', 50),
            ])
        ;

        // Register init command
        $container->register(InitCommand::class, InitCommand::class)
            ->addArgument(new Reference($className))
            ->addTag('console.command')
        ;

        if (is_subclass_of($className, FailedJobRepositoryInterface::class)) {
            return $className;
        }

        // Compatibility with legacy prime driver : adapt to repository interface
        $container->register('bdf_queue.failer.prime_repository', FailedJobRepositoryAdapter::class)
            ->setFactory([FailedJobRepositoryAdapter::class, 'adapt'])
            ->setArguments([new Reference($className)])
        ;

        return 'bdf_queue.failer.prime_repository';
    }
}
