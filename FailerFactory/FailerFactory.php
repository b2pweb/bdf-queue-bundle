<?php

namespace Bdf\QueueBundle\FailerFactory;

use Bdf\Dsn\Dsn;
use Bdf\Queue\Failer\FailedJobRepositoryInterface;
use InvalidArgumentException;

/**
 * Factory for failer storage
 */
final class FailerFactory
{
    /**
     * Factories indexed by the scheme
     *
     * @var array<string, FailerDsnFactoryInterface>
     * @see FailerDsnFactoryInterface::scheme() For the key
     */
    private $factories = [];

    /**
     * Register a new factory
     *
     * @param FailerDsnFactoryInterface $factory
     *
     * @return void
     */
    public function register(FailerDsnFactoryInterface $factory): void
    {
        $this->factories[$factory->scheme()] = $factory;
    }

    /**
     * Try to create the repository using a DSN string
     *
     * @param string $dsn The repository DSN
     *
     * @return FailedJobRepositoryInterface
     *
     * @throws InvalidArgumentException When the repository cannot be created
     */
    public function createFromDsn(string $dsn): FailedJobRepositoryInterface
    {
        $parsedDsn = Dsn::parse($dsn);

        $factory = $this->factories[$parsedDsn->getScheme()] ?? null;

        if (!$factory) {
            throw new InvalidArgumentException('Unsupported failer scheme "' . $parsedDsn->getScheme() . '"');
        }

        return $factory->create($parsedDsn);
    }
}
