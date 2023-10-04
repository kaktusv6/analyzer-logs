<?php

namespace Tests\UseCases;

use App\Metrics\ICollector;
use App\Metrics\ICounter;
use App\Metrics\IHistogram;
use App\UseCases\AnalyzerAvailableService;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class AnalyzerAvailableServiceTest extends TestCase
{
    private Generator $faker;
    private Carbon $startedAt;

    private ICounter $counter;
    private ICounter $counterSuccessRequests;
    private ICollector $collector;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->faker = Factory::create();
        $this->startedAt = Carbon::now()->days(-1);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->counter = $this->createMock(ICounter::class);
        $this->counterSuccessRequests = $this->createMock(ICounter::class);
        $histogram = $this->createMock(IHistogram::class);
        $this->collector = $this->createMock(ICollector::class);

        $histogram
            ->expects($this->once())
            ->method('getCounters')
            ->willReturn([$this->counterSuccessRequests])
        ;

        $this->collector
            ->expects($this->once())
            ->method('getCounter')
            ->willReturn($this->counter)
        ;
        $this->collector
            ->expects($this->once())
            ->method('getHistogram')
            ->willReturn($histogram)
        ;
    }

    public function dataProviderForTestSimple(): array
    {
        return [
            [1, 100],
            [10, 99],
            [100, 90],
        ];
    }

    /** @dataProvider dataProviderForTestSimple */
    public function testSimple(int $countRequests, float $precent): void
    {
        $counterData = $this->generateCounterData($countRequests);
        $counterSuccessRequestData = $this->generateCounterSuccessRequestData($counterData);

        $this->counter
            ->expects($this->once())
            ->method('get')
            ->willReturn($counterData)
        ;
        $this->counterSuccessRequests
            ->expects($this->exactly($countRequests))
            ->method('getByTime')
            ->will($this->returnCallback(function (int $time) use ($counterSuccessRequestData) {
                return $counterSuccessRequestData[$time];
            }))
        ;

        $analyzer = new AnalyzerAvailableService();

        $result = $analyzer->analyze(
            $this->collector,
            $precent,
        );

        $this->assertIsArray($result);
        $this->assertTrue(count($result) <= $countRequests);
    }

    private function generateCounterData(int $count): array
    {
        $result = [];
        $time = $this->startedAt->unix();
        while (count($result) < $count) {
            $result[$time] = $this->faker->numberBetween(1);

            $time += $this->faker->numberBetween(0, 5);
        }

        return $result;
    }

    private function generateCounterSuccessRequestData(array $counterData): array
    {
        $result = [];
        foreach ($counterData as $time => $counter) {
            $result[$time] = $this->faker->numberBetween(0, $counter);
        }

        return $result;
    }
}
