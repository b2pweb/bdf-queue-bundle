<?php
namespace Bdf\QueueBundle\Tests;

require_once __DIR__.'/TestKernel.php';

use Bdf\Queue\Connection\Prime\PrimeConnection;
use Bdf\Queue\Console\Command\Failer\AbstractFailerCommand;
use Bdf\Queue\Failer\DbFailedJobStorage;
use Bdf\Queue\Failer\FailedJobRepositoryAdapter;
use Bdf\Queue\Failer\FailedJobStorageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class WithPrimeTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(PrimeConnection::class)) {
            $this->markTestSkipped('b2pweb/bdf-queue-prime-adapter not installed');
        }
    }

    /**
     * @return void
     */
    public function test_prime_failer()
    {
        $kernel = new \TestKernel(__DIR__ . '/Fixtures/conf_with_prime_failer.yaml');
        $kernel->boot();
        $console = new Application($kernel);

        $command = $console->get('queue:failer:delete');

        $r = new \ReflectionProperty(AbstractFailerCommand::class, 'repository');
        $r->setAccessible(true);

        /** @var FailedJobStorageInterface $failer */
        $failer = $r->getValue($command);

        $this->assertTrue($this->isPrimeFailer($failer));
    }

    private function isPrimeFailer($storage): bool
    {
        if ($storage instanceof DbFailedJobRepository) {
            return true;
        }

        if ($storage instanceof FailedJobRepositoryAdapter) {
            $r = new \ReflectionProperty(FailedJobRepositoryAdapter::class, 'storage');
            $r->setAccessible(true);

            return $r->getValue($storage) instanceof DbFailedJobStorage;
        }

        return false;
    }
}
