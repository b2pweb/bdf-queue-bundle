<?php

namespace Bdf\QueueBundle\FailerFactory;

use Bdf\Dsn\DsnRequest;
use Bdf\Queue\Failer\FailedJobRepositoryInterface;

/**
 * Factory for a single failer storage driver
 */
interface FailerDsnFactoryInterface
{
    /**
     * The DSN scheme
     *
     * @return string
     */
    public function scheme(): string;

    /**
     * Create the repository according to the DSN
     *
     * @param DsnRequest $dsn The parsed DSN
     *
     * @return FailedJobRepositoryInterface
     */
    public function create(DsnRequest $dsn): FailedJobRepositoryInterface;
}
