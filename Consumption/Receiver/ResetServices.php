<?php

namespace Bdf\QueueBundle\Consumption\Receiver;

use Bdf\Queue\Consumer\ConsumerInterface;
use Bdf\Queue\Consumer\DelegateHelper;
use Bdf\Queue\Consumer\ReceiverInterface;
use Symfony\Component\DependencyInjection\ServicesResetter as ContainerResetter;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetterInterface;

/**
 * Reset all services registered on "kernel.reset".
 */
class ResetServices implements ReceiverInterface
{
    use DelegateHelper;

    public function __construct(private ServicesResetter|ServicesResetterInterface|ContainerResetter $servicesResetter)
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
