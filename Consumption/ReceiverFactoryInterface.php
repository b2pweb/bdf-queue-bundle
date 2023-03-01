<?php

namespace Bdf\QueueBundle\Consumption;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\ReceiverInterface;

/**
 * Provides a factory to create your own receiver.
 * This interface is auto configured. The factory will be added to the ReceiverFactory.
 */
interface ReceiverFactoryInterface
{
    /**
     * Returns all the name and aliases of the receiver.
     *
     * @return string[]
     */
    public function getReceiverNames(): array;

    /**
     * Create the receiver.
     *
     * @param ReceiverFactory $factory   The factory of receivers. Contains the logger.
     * @param array           $arguments The arguments usually provide by the builder
     */
    public function create(ReceiverFactory $factory, ...$arguments): ReceiverInterface;
}
