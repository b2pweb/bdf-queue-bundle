<?php

namespace Bdf\QueueBundle\FailerFactory;

use Bdf\Dsn\DsnRequest;
use Bdf\Queue\Failer\FailedJobRepositoryInterface;
use Bdf\Queue\Failer\MemoryFailedJobRepository;

/**
 * Factory for `MemoryFailedJobRepository`
 * Does not take parameters
 *
 * DSN format: "memory:"
 */
final class MemoryFailerFactory implements FailerDsnFactoryInterface
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
    public function create(DsnRequest $dsn): FailedJobRepositoryInterface
    {
        return new MemoryFailedJobRepository();
    }
}
