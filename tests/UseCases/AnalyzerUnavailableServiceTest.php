<?php

namespace Tests\UseCases;

use App\Metrics\CollectorInMemory;
use App\UseCases\AnalyzerUnavailableService;
use App\UseCases\CreatorIntervals;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class AnalyzerUnavailableServiceTest extends TestCase
{
    private AnalyzerUnavailableService $analyzer;
    private string $pathToLogs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->analyzer = new AnalyzerUnavailableService(
            new CollectorInMemory(),
            new CreatorIntervals(),
        );
        $this->pathToLogs = __DIR__ . '/../../resources/logs/access.test.log';
    }

    public function dataProviderForTestSimple(): array
    {
        return [
            [99, 45, 1],
            [90, 45, 1],
            [90, 50, 3],
            [70, 50, 0],
            [35, 20, 2],
            [30, 10, 1],
        ];
    }

    /** @dataProvider dataProviderForTestSimple */
    public function testSimple(float $present, float $time, int $expectedCount): void
    {
        $result = $this->analyzer->analyze(
            $present,
            $time,
            $this->pathToLogs,
        );

        $this->assertCount($expectedCount, $result);

        $this->markTestIncomplete('Логика алгоритма отличается от потокового алгоритма');
    }

    public function dataProviderForTestFlow(): array
    {
        return [
            [99, 45, 2],
            [90, 45, 2],
            [90, 50, 1],
            [70, 50, 0],
            [35, 20, 8],
            [30, 10, 6],
        ];
    }

    /** @dataProvider dataProviderForTestFlow */
    public function testFlow(float $present, float $time, int $expectedCount): void
    {
        $actualCount = 0;
        foreach ($this->analyzer->analyzeFlow(
            $present,
            $time,
            $this->pathToLogs,
        ) as $_) {
            $actualCount++;
        }

        $this->assertEquals($expectedCount, $actualCount);
    }
}
