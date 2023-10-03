<?php

namespace App\Metrics;

interface ICollector
{
    public function createCounter(string $name, array $labels = []): ICounter;

    /**
     * @param float[] $buckets
     */
    public function createHistogram(string $name, array $buckets = [], array $labels = []): IHistogram;

    public function getHistogram(): IHistogram;

    public function getCounter(): ICounter;
}
