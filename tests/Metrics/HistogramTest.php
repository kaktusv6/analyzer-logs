<?php

namespace Tests\Metrics;

use App\Metrics\HistogramInMemory;
use Carbon\Carbon;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class HistogramTest extends TestCase
{
    private HistogramInMemory $histogram;
    private array $labels = [200,400,500];
    private array $buckets = [10, 20];

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }


    protected function setUp(): void
    {
        parent::setUp();

        $faker = Factory::create();

        $this->labels = [200, 400, 500];
        $this->buckets = [10, 20];

        $this->histogram = new HistogramInMemory('test_histogram', $this->buckets, ['status']);
        $startedAt = Carbon::now()->days(-1);

        $i = 0;
        while ($i < 10) {
            $le = $faker->randomFloat(4, 0, 25);

            $this->histogram->set(
                $startedAt,
                $le,
                [
                    $faker->randomElement($this->labels),
                ]
            );
            $startedAt->addSeconds();
            $i++;
        }
    }

    public function testSimple(): void
    {
        $counters = $this->histogram->getCounters();

        $this->assertCount(count($this->labels), $counters);

        foreach ($this->buckets as $index => $_) {
            $counter = $counters[$index];
            $this->assertCount(0, $counter->get());
        }
    }
}
