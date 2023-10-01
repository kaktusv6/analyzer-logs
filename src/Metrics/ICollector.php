<?php

namespace App\Metrics;

interface ICollector
{
    /** @param string[] $labels */
    public function createCounter(string $name, array $labels = []): ICounter;

    /**
     * @param float[]  $buckets
     * @param string[] $labels
     */
    public function createHistogram(string $name, array $buckets = [], array $labels = []): IHistogram;

    public function getHistogram(): IHistogram;

    public function getCounter(): ICounter;
}
