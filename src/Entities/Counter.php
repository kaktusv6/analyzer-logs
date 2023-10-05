<?php

namespace App\Entities;

final class Counter
{
    private int $value = 0;

    public function __construct(
        private string $label,
    ) {

    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function inc(int $value = 1): void
    {
        $this->value += $value;
    }
}
