<?php

namespace Tests;

use App\Metrics\CounterInMemory;
use App\Metrics\ICounter;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CounterTest extends TestCase
{
    private ICounter $counter;
    private Carbon $dateTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->counter = new CounterInMemory('test_counter');
        $this->dateTime = Carbon::now()->days(-1);
    }

    public function dataProviderSeconds(): array
    {
        return [
            [1, 200],
            [5, 500],
            [10, 'POST'],
        ];
    }

    /** @dataProvider dataProviderSeconds */
    public function testIncWithoutLabels(int $seconds, int|string $label): void
    {
        $i = 0;
        while ($i < $seconds) {
            $this->counter->inc($this->dateTime);
            $this->dateTime->addSeconds();
            ++$i;
        }

        $this->assertCount($seconds, $this->counter->get());
        $this->assertCount(0, $this->counter->get([$label]));
    }

    /** @dataProvider dataProviderSeconds */
    public function testIncWithLabel(int $seconds, int|string $label): void
    {
        $i = 0;
        while ($i < $seconds) {
            $this->counter->inc($this->dateTime, [$label]);
            $this->dateTime->addSeconds();
            ++$i;
        }

        $this->assertCount(0, $this->counter->get());
        $this->assertCount($seconds, $this->counter->get([$label]));
    }
}
