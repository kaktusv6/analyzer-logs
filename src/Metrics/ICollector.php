<?php

namespace App\Metrics;

interface ICollector
{
    public function createCounter(string $name): ICounter;

    /**
     * @param float[] $buckets
     */
    public function createHistogram(string $name, array $buckets = []): IHistogram;

    public function getHistogram(): IHistogram;

    public function getCounter(): ICounter;
}
