<?php

namespace App\Metrics;

use Carbon\Carbon;

final class CounterInMemory implements ICounter
{
    private array $counterMap = [];

    /** @var int[] */
    private array $counters = [];

    /** @param string[] $labels */
    public function __construct(
        private string $name = 'default_counter',
        private array $labels = [],
    ) {
    }

    public function inc(Carbon $dateTime, array $labels = []): void
    {
        $key = $this->getKeyByLabels($labels);

        $counter = $this->counters[$key] ?? 0;
        ++$counter;

        $this->counterMap[$key][$dateTime->unix()] = $counter;
        $this->counters[$key] = $counter;
    }

    public function get(array $labels = []): array
    {
        return $this->counterMap[$this->getKeyByLabels($labels)] ?? [];
    }

    public function getByTime(int $time, array $labels = []): int
    {
        $map = $this->get($labels);

        $result = $map[$time] ?? null;

        $minTime = array_key_first($map) ?? $time;

        while (null === $result && $time >= $minTime) {
            --$time;
            $result = $map[$time] ?? null;
        }

        return $result ?? 0;
    }

    private function getKeyByLabels(array $labels = []): string
    {
        return join(',', $labels);
    }
}
