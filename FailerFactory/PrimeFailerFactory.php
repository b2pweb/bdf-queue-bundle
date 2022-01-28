<?php

namespace Bdf\QueueBundle\FailerFactory;

use Bdf\Dsn\DsnRequest;
use Bdf\Prime\ServiceLocator;
use Bdf\Queue\Failer\DbFailedJobRepository;
use Bdf\Queue\Failer\DbFailedJobStorage;
use Bdf\Queue\Failer\FailedJobRepositoryAdapter;
use Bdf\Queue\Failer\FailedJobRepositoryInterface;
use InvalidArgumentException;

/**
 * Factory for storage using prime
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
final class PrimeFailerFactory implements FailerDsnFactoryInterface
{
    /**
     * @var ServiceLocator
     */
    private $prime;

    /**
     * @param ServiceLocator $prime
     */
    public function __construct(ServiceLocator $prime)
    {
        $this->prime = $prime;
    }

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
    public function create(DsnRequest $dsn): FailedJobRepositoryInterface
    {
        if (!$host = $dsn->getHost()) {
            throw new InvalidArgumentException('The connection name is required on prime failer DSN');
        }

        if (!$path = trim($dsn->getPath(), '/')) {
            throw new InvalidArgumentException('The table name is required on prime failer DSN');
        }

        $schema = [
            'connection' => $host,
            'table' => $path
        ];
        $maxRows = (int) $dsn->query('maxRows', 50);

        if (class_exists(DbFailedJobRepository::class)) {
            return DbFailedJobRepository::make($this->prime, $schema, $maxRows);
        } else {
            return FailedJobRepositoryAdapter::adapt(DbFailedJobStorage::make($this->prime, $schema, $maxRows));
        }
    }

    /**
     * Check if the prime repository is supported (i.e. package "b2pweb/bdf-queue-prime-adapter" is installed)
     *
     * @return bool
     */
    public static function supported(): bool
    {
        return class_exists(DbFailedJobRepository::class) || class_exists(DbFailedJobStorage::class);
    }
}
