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

    /**
     * @var ServicesResetter
     */
    private $servicesResetter;

    public function __construct(ReceiverInterface $delegate, ServicesResetter $servicesResetter)
    {
        $this->delegate = $delegate;
        $this->servicesResetter = $servicesResetter;
    }

    public function receive($message, ConsumerInterface $consumer): void
    {
        try {
            $this->delegate->receive($message, $consumer);
        } finally {
            $this->servicesResetter->reset();
        }
    }
}
