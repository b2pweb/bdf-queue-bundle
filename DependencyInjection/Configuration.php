<?php

namespace Bdf\QueueBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * @param bool $debug Whether to use the debug mode
     */
    public function __construct($debug = false)
    {
        $this->debug = (bool) $debug;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('bdf_queue');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('default_connection')->defaultNull()->end()
                ->scalarNode('default_serializer')->defaultValue('bdf')->end()
                ->scalarNode('failer')
                    ->info('A DSN with failer configuration; Format: [memory|prime]://{connection}/{table}')
                    ->defaultValue('memory:')
                    ->cannotBeEmpty()
                ->end()
                ->append($this->getConnectionsNode())
                ->append($this->getDestinationsNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return NodeDefinition
     */
    private function getConnectionsNode()
    {
        $root = (new TreeBuilder('connections'))->getRootNode();
        $root
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->isRequired()
            ->beforeNormalization()
                ->ifString()
                ->then(static function ($v) {
                    return ['url' => $v];
                })
            ->end()
            ->children()
                ->scalarNode('url')
                    ->info('A URL with connection information; any parameter value parsed from this string will override explicitly set parameters. Format: {driver}+{vendor}://{user}:{password}@{host}:{port}/{queue}?{option}=value')
                    ->defaultNull()
                ->end()
                ->scalarNode('driver')->defaultNull()->end()
                ->scalarNode('vendor')->defaultNull()->end()
                ->scalarNode('queue')->defaultNull()->end()
                ->scalarNode('host')->defaultNull()->end()
                ->scalarNode('port')->defaultNull()->end()
                ->scalarNode('user')->defaultNull()->end()
                ->scalarNode('password')->defaultNull()->end()
                ->arrayNode('serializer')
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($v) {
                            return ['id' => $v];
                        })
                    ->end()
                    ->children()
                        ->scalarNode('id')
                            ->info('The serializer ID. This ID will be prefix by "bdf_queue.serializer". Defined values: native, bdf, bdf_json.')
                        ->end()
                        ->scalarNode('service')
                            ->info('The serializer service ID.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('options')
                    ->useAttributeAsKey('key')
                    ->variablePrototype()->end()
                ->end()
                ->scalarNode('connection_factory')
                    ->defaultNull()
                ->end()
            ->end();

        return $root;
    }

    /**
     * @return NodeDefinition
     */
    private function getDestinationsNode()
    {
        $root = (new TreeBuilder('destinations'))->getRootNode();
        $root
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->isRequired()
            ->beforeNormalization()
                ->ifString()
                ->then(static function ($v) {
                    return ['url' => $v];
                })
            ->end()
            ->children()
                ->scalarNode('url')
                    ->info('A URL with destination information; Format: [queue|queues|topic]://{connection}/{queue}')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('consumer')
                    ->children()
                        ->scalarNode('handler')
                            ->info('Set unique handler as outlet receiver')
                            ->defaultNull()->end()
                        ->scalarNode('retry')
                            ->info('Retry failed jobs (i.e. throwing an exception)')
                            ->defaultNull()->end()
                        ->scalarNode('max')
                            ->info('Limit the number of received message. When the limit is reached, the consumer is stopped')
                            ->defaultNull()->end()
                        ->scalarNode('limit')
                            ->info('Limit the received message rate')
                            ->defaultNull()->end()
                        ->scalarNode('memory')
                            ->info('Limit the total memory usage of the current runtime in bytes. When the limit is reached, the consumer is stopped')
                            ->defaultNull()->end()
                        ->booleanNode('save')
                            ->info('Store the failed messages')
                            ->defaultNull()->end()
                        ->booleanNode('no_failure')
                            ->info('Catch all exceptions to ensure that the consumer will no crash (and will silently fail)')
                            ->defaultNull()->end()
                        ->booleanNode('no_reset')
                            ->info('Disable the reset of services between messages.')
                            ->defaultNull()->end()
                        ->booleanNode('stop_when_empty')
                            ->info('Stops consumption when the destination is empty (i.e. no messages are received during the waiting duration)')
                            ->defaultNull()->end()
                        ->booleanNode('auto_handle')
                            ->info('Set auto discover as outlet receiver. The message should contain target hint.')
                            ->defaultNull()->end()
                        ->arrayNode('middlewares')
                            ->useAttributeAsKey('name')
                            ->variablePrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $root;
    }
}
