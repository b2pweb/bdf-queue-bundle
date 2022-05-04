<?php

namespace Bdf\QueueBundle\Consumption;

use Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory;
use Bdf\Queue\Consumer\ReceiverInterface;

/**
 * Sub factory of connection driver. Create your own factory with this interface to create your own connection driver.
 */
interface ReceiverFactoryProviderInterface
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
     * @param ReceiverFactory $factory  The factory of receivers. Contains the logger.
     * @param ReceiverInterface $receiver The next receiver of the stack.
     * @param array $arguments The arguments usually provide by the builder.
     *
     * @return ReceiverInterface
     */
    public function create(ReceiverFactory $factory, ReceiverInterface $receiver, ...$arguments): ReceiverInterface;
}