<?php

namespace App\Metrics;

use Carbon\Carbon;

interface ICounter
{
    public function inc(Carbon $dateTime, array $labels = []): void;

    public function get(array $labels = []): array;

    public function getByTime(int $time, array $labels = []): int;
}
