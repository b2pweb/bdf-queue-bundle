<?php

namespace Bdf\QueueBundle\DependencyInjection\Compiler;

use Bdf\Dsn\Dsn;
use Bdf\Queue\Failer\FailedJobRepositoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register the failer repository on the container and configure the alias to point to the configured failer
 * This compiler pass will parse the failer dsn, and check for configurator drivers registered
 * with tag "bdf_queue.failer.driver_configurator".
 */
final class RegisterFailerDriverPass implements CompilerPassInterface
{
    public const CONFIGURATOR_TAG_NAME = 'bdf_queue.failer.driver_configurator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $dsn = Dsn::parse($container->getParameter('bdf_queue.failer_dsn'));
        $scheme = $dsn->getScheme();
        $availableScheme = [];

        foreach ($container->findTaggedServiceIds(self::CONFIGURATOR_TAG_NAME) as $serviceId => $tag) {
            /** @var FailerDriverConfiguratorInterface $configurator */
            $configurator = $container->get($serviceId);

            if (!$configurator->available($container)) {
                continue;
            }

            $availableScheme[] = $configurator->scheme();
            if ($scheme === $configurator->scheme()) {
                $container->setAlias(FailedJobRepositoryInterface::class, $configurator->configure($dsn, $container));

                return;
            }
        }

        throw new \InvalidArgumentException('Unsupported failer DSN scheme '.$scheme.'. Available : '.implode(', ', $availableScheme));
    }
}
