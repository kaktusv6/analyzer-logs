<?php

namespace App\ValueObjects;

final class Interval
{
    public function __construct(
        private int $start,
        private int $end,
    ) {
        $this->assert($start, $end);
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

    private function assert(int $start, int $end): void
    {
        if ($start > $end) {
            throw new \InvalidArgumentException('Начало интервала не должно быть меньше чем окончание');
        }
    }
}
