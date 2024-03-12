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
                            ],
                        ],
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
                                'bind' => [],
                                'middlewares' => [],
                            ],
                        ],
                    ],
                    'default_connection' => null,
                    'default_serializer' => 'bdf',
                    'failer' => 'memory:',
                    'connections' => [],
                ],
            ],
            [
                'with bind',
                [
                    'destinations' => [
                        'foo' => [
                            'consumer' => [
                                'bind' => [
                                    'Foo' => 'App\Event\Foo',
                                    'Bar' => 'App\Event\Bar',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'destinations' => [
                        'foo' => [
                            'consumer' => [
                                'bind' => [
                                    'Foo' => 'App\Event\Foo',
                                    'Bar' => 'App\Event\Bar',
                                ],
                                'handler' => null,
                                'retry' => null,
                                'max' => null,
                                'limit' => null,
                                'memory' => null,
                                'save' => null,
                                'no_failure' => null,
                                'no_reset' => null,
                                'stop_when_empty' => null,
                                'auto_handle' => null,
                                'middlewares' => [],
                            ],
                        ],
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
    public function testValidConfig(string $description, array $config, array $expected)
    {
        $cfg = new Configuration();
        $tree = $cfg->getConfigTreeBuilder()->buildTree();
        $result = $tree->finalize($tree->normalize($config));

        self::assertSame($expected, $result, $description);
    }
}
