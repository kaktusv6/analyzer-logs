<?php

namespace App\ValueObjects;

final class Interval
{
    private int $start;
    private int $end;

    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function isToOnePoint(): bool
    {
        return $this->getStart() === $this->getEnd();
    }
}
