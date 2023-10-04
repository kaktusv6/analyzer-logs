<?php

namespace Tests\UseCases;

use App\Entities\AccessLog;
use App\Metrics\ICollector;
use App\Metrics\ICounter;
use App\Metrics\IHistogram;
use App\Repositories\IAccessLogRepository;
use App\UseCases\FillerRequestMetrics;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class FillRequestMetricsTest extends TestCase
{
    private Generator $faker;

    private IAccessLogRepository $repository;
    private ICounter $counter;
    private IHistogram $histogram;
    private ICollector $collector;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->faker = Factory::create();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(IAccessLogRepository::class);

        $this->counter = $this->createMock(ICounter::class);
        $this->histogram = $this->createMock(IHistogram::class);
        $this->collector = $this->createMock(ICollector::class);
    }

    public function dataProviderLogs(): array
    {
        return [
            'first test' => [$this->generateLogs(6)],
            'second test' => [$this->generateLogs(10)],
            'one log' => [$this->generateLogs(1)],
            'empty logs' => [[]],
        ];
    }

    /** @dataProvider dataProviderLogs */
    public function testSimple(array $logs): void
    {
        $logsChunks = array_chunk($logs, 5);

        $this->repository
            ->expects($this->once())
            ->method('getChunksGenerator')
            ->will($this->returnCallback(function () use ($logsChunks) {
                foreach ($logsChunks as $chunk) {
                    yield $chunk;
                }
            }))
        ;

        $countLogs = count($logs);

        $this->counter
            ->expects($this->exactly($countLogs))
            ->method('inc')
        ;

        $this->histogram
            ->expects($this->exactly($countLogs))
            ->method('set')
        ;

        $this->collector
            ->expects($this->exactly($countLogs))
            ->method('getCounter')
            ->willReturn($this->counter)
        ;

        $this->collector
            ->expects($this->exactly($countLogs))
            ->method('getHistogram')
            ->willreturn($this->histogram)
        ;

        $filler = new FillerRequestMetrics(
            $this->repository,
        );

        $collector = $filler->byLogs($this->collector);
        $this->assertEquals($this->collector, $collector);
    }

    private function generateLogs(int $count): array
    {
        $startedAt = Carbon::now()->days(-1);

        $result = [];

        while (count($result) < $count) {
            $result[] = (new AccessLog())
                ->setStatus($this->faker->randomElement([200, 500]))
                ->setTimeMilliseconds($this->faker->randomFloat(1, 50))
                ->setCreatedAt($startedAt)
            ;
            $startedAt->addSeconds($this->faker->randomElement([0, 1]));
        }

        return $result;
    }
}
