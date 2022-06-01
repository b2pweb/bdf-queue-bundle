<?php

namespace Bdf\QueueBundle\Tests\DependencyInjection;

use Bdf\QueueBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function configDataProvider(): array
    {
        return [
            [
                'empty',
                [
                ],
                [
                    'default_connection' => null,
                    'default_serializer' => 'bdf',
                    'failer' => 'memory:',
                    'connections' => [],
                    'destinations' => [],
                ],
            ],
            [
                'consumer options',
                [
                    'destinations' => [
                        'foo' => [
                            'consumer' => [
                                'no_reset' => true,
                                'retry' => 2,
                                'max' => 3,
                                'limit' => 4,
                                'memory' => 128,
                                'save' => true,
                                'no_failure' => true,
                                'stop_when_empty' => true,
                                'auto_handle' => false,
                            ]
                        ]
                    ],
                ],
                [
                    'destinations' => [
                        'foo' => [
                            'consumer' => [
                                'no_reset' => true,
                                'retry' => 2,
                                'max' => 3,
                                'limit' => 4,
                                'memory' => 128,
                                'save' => true,
                                'no_failure' => true,
                                'stop_when_empty' => true,
                                'auto_handle' => false,
                                'handler' => null,
                                'middlewares' => [],
                            ]
                        ]
                    ],
                    'default_connection' => null,
                    'default_serializer' => 'bdf',
                    'failer' => 'memory:',
                    'connections' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider configDataProvider
     */
    public function test_valid_config(string $description, array $config, array $expected)
    {
        $cfg = new Configuration();
        $tree = $cfg->getConfigTreeBuilder()->buildTree();
        $result = $tree->finalize($tree->normalize($config));

        self::assertSame($expected, $result, $description);
    }
}
