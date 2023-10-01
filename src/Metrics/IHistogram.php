<?php

namespace App\Metrics;

use Carbon\Carbon;

interface IHistogram
{
    public function set(Carbon $createdAt, float $le, array $labels = []): void;

    /** @return ICounter[] */
    public function getCounters(): array;
}
