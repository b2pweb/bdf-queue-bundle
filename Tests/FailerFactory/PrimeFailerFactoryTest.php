<?php

namespace Bdf\QueueBundle\Tests\FailerFactory;

use Bdf\Dsn\Dsn;
use Bdf\Prime\Prime;
use Bdf\Queue\Connection\Prime\PrimeConnection;
use \Bdf\Queue\Failer\DbFailedJobRepository;
use Bdf\Queue\Failer\DbFailedJobStorage;
use Bdf\Queue\Failer\FailedJobRepositoryAdapter;
use Bdf\QueueBundle\FailerFactory\PrimeFailerFactory;
use PHPUnit\Framework\TestCase;

class PrimeFailerFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(PrimeConnection::class)) {
            $this->markTestSkipped('b2pweb/bdf-queue-prime-adapter not installed');
        }

        if (!Prime::isConfigured()) {
            Prime::configure([
                'connection' => [
                    'config' => [
                        'test' => [
                            'adapter' => 'sqlite',
                            'memory' => true
                        ],
                    ]
                ],
            ]);
        }
    }

    protected function tearDown(): void
    {
        Prime::configure(null);
    }

    /**
     * @return void
     */
    public function test_scheme()
    {
        $factory = new PrimeFailerFactory(Prime::service());

        $this->assertSame('prime', $factory->scheme());
    }

    /**
     * @return void
     */
    public function test_create_success()
    {
        $factory = new PrimeFailerFactory(Prime::service());

        $repository = $factory->create(Dsn::parse('prime://test/failed_jobs?maxRows=15'));

        if (class_exists(DbFailedJobRepository::class)) {
            $this->assertInstanceOf(DbFailedJobRepository::class, $repository);
            $this->assertEquals(DbFailedJobRepository::make(Prime::service(), ['connection' => 'test', 'table' => 'failed_jobs'], 15));
        } else {
            $this->assertInstanceOf(FailedJobRepositoryAdapter::class, $repository);
            $this->assertEquals(
                FailedJobRepositoryAdapter::adapt(DbFailedJobStorage::make(Prime::service(), ['connection' => 'test', 'table' => 'failed_jobs'], 15)),
                $repository
            );
        }
    }

    /**
     * @return void
     */
    public function test_create_missing_host()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The connection name is required on prime failer DSN');

        $factory = new PrimeFailerFactory(Prime::service());

        $factory->create(Dsn::parse('prime:'));
    }

    /**
     * @return void
     */
    public function test_create_missing_table()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table name is required on prime failer DSN');

        $factory = new PrimeFailerFactory(Prime::service());

        $factory->create(Dsn::parse('prime://connection/'));
    }
}
