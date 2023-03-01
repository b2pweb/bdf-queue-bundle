<?php

namespace Bdf\QueueBundle\Consumption\Receiver;

use Bdf\Queue\Consumer\ConsumerInterface;
use Bdf\Queue\Consumer\DelegateHelper;
use Bdf\Queue\Consumer\ReceiverInterface;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

/**
 * Reset all services registered on "kernel.reset".
 */
class ResetServices implements ReceiverInterface
{
    use DelegateHelper;

    public function __construct(private ServicesResetter $servicesResetter)
    {
    }

    public function receive($message, ConsumerInterface $next): void
    {
        try {
            $next->receive($message, $next);
        } finally {
            $this->servicesResetter->reset();
        }
    }
}
