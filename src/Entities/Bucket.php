<?php

namespace App\Entities;

final class Bucket
{
    /** @var array<string, Counter> */
    private array $counters = [];

    public function __construct(
        private float $le,
        private array $labels = [],
    ) {
        foreach ($this->labels as $label) {
            $this->counters[$label] = new Counter($label);
        }
    }

    public function inc(string $label)
    {
        $this->getCounter($label)->inc();
    }

    public function getLe(): float
    {
        return $this->le;
    }

    public function getCounter(string $label): Counter
    {
        return $this->counters[$label];
    }
}
