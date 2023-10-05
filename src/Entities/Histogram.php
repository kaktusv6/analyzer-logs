<?php

namespace App\Entities;

use Carbon\Carbon;

class Histogram
{
    private int $count = 0;

    public function __construct(
        private Carbon $time,
        /** @var Bucket[] */
        private array  $buckets,
    ) {
    }

    public function fix(float $le, string $label): void
    {
        $this->count++;
        $this->getBucket($le)->inc($label);
    }

    public function getBucket(float $le): Bucket
    {
        $result = $this->buckets[array_key_last($this->buckets)];

        foreach ($this->buckets as $bucket) {
            if ($le <= $bucket->getLe()) {
                $result = $bucket;
                break;
            }
        }

        return $result;
    }

    public function getTime(): Carbon
    {
        return $this->time;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $value): Histogram
    {
        $this->count += $value;

        return $this;
    }
}
