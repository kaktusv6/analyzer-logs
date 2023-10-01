<?php

namespace App\Metrics;

use Carbon\Carbon;

final class HistogramInMemory implements IHistogram
{
    private array $buckets = [];
    private array $labels = [];

    /** @var ICounter[] */
    private array $counters = [];

    public function __construct(
        private string $name = 'default',
        array $buckets = [10, 20, 30],
        array $labels = []
    ) {
        $this->buckets = array_merge($buckets, [PHP_INT_MAX]);
        $this->labels = array_merge($labels, ['le']);
        $this->name = join('_', [$this->name, 'histogram']);

        foreach ($this->buckets as $bucket) {
            $this->counters[$bucket] = new CounterInMemory(
                join('_', [$this->name, 'bucket']),
                $labels,
            );
        }
    }

    public function set(Carbon $createdAt, float $le, array $labels = []): void
    {
        foreach ($this->counters as $bucket => $counter) {
            if ($le <= $bucket) {
                $counter->inc($createdAt, $labels);
            }
        }
    }

    public function getCounters(): array
    {
        return array_values($this->counters);
    }
}
