<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;

class TestKernel extends Symfony\Component\HttpKernel\Kernel
{
    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

    private $config;

    public function __construct(?string $config = null)
    {
        parent::__construct($config ? dechex(crc32($config)) : 'dev', true);

        $this->config = $config;
    }

    public function registerBundles(): iterable
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Bdf\QueueBundle\BdfQueueBundle(),
            new Bdf\PrimeBundle\PrimeBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $loader->load($this->config ?? __DIR__.'/Fixtures/conf.yaml');
    }

    // PHP 7.1
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
    }
}
