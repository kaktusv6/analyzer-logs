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
    protected function setUp(): void
    {
        parent::setUp();

        $this->analyzer = new AnalyzerUnavailableService(
            new CollectorInMemory(),
            new CreatorIntervals(),
        );
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
    public function testSimple(float $precent, float $time, int $countIntervals): void
    {
        $result = $this->analyzer->analyze(
            $precent,
            $time,
            __DIR__.'/../../resources/logs/access.test.log',
        );

        $this->assertCount($countIntervals, $result);
    }
}
