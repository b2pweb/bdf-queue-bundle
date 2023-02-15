<?php

namespace Bdf\QueueBundle\Tests\Fixtures;

use Bdf\Queue\Destination\ConfigurationDestinationFactory;
use Bdf\Queue\Destination\DestinationFactoryInterface;
use Bdf\Queue\Destination\DsnDestinationFactory;

class GetDestinationFactory
{
    public $destinationFactory;
    public $legacyDsnDestinationFactory;
    public $legacyConfigurationDestinationFactory;

    public function __construct(DestinationFactoryInterface $destinationFactory, DsnDestinationFactory $dsnDestinationFactory, ConfigurationDestinationFactory $configurationDestinationFactory)
    {
        $this->destinationFactory = $destinationFactory;
        $this->legacyDsnDestinationFactory = $dsnDestinationFactory;
        $this->legacyConfigurationDestinationFactory = $configurationDestinationFactory;
    }
}
