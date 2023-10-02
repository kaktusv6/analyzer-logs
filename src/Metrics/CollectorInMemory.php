<?php

namespace App\Metrics;

final class CollectorInMemory implements ICollector
{
    private ICounter $counter;
    private IHistogram $histogram;

    public function createCounter(string $name): ICounter
    {
        $this->counter = new CounterInMemory(
            $name,
        );

        return $this->counter;
    }

    public function createHistogram(string $name, array $buckets = []): IHistogram
    {
        $this->histogram = new HistogramInMemory(
            $name,
            $buckets
        );

        return $this->histogram;
    }

    public function getCounter(): ICounter
    {
        return $this->counter;
    }

    public function getHistogram(): IHistogram
    {
        return $this->histogram;
    }
}
