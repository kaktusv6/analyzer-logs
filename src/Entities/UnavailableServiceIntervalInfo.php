<?php

namespace App\Entities;

use Carbon\Carbon;

final class UnavailableServiceIntervalInfo
{
    private Carbon $startedAt;
    private Carbon $endedAt;
    private float $present;

    public function __toString(): string
    {
        return sprintf(
            '%s %s %01.2f',
            $this->getStartedAt(),
            $this->getEndedAt(),
            $this->getPresent()
        );
    }

    public function getPresent(): float
    {
        return $this->present;
    }

    public function setPresent(float $present): UnavailableServiceIntervalInfo
    {
        $this->present = $present;

        return $this;
    }

    public function getStartedAt(): Carbon
    {
        return $this->startedAt;
    }

    public function setStartedAt(Carbon $startedAt): UnavailableServiceIntervalInfo
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): Carbon
    {
        return $this->endedAt;
    }

    public function setEndedAt(Carbon $endedAt): UnavailableServiceIntervalInfo
    {
        $this->endedAt = $endedAt;

        return $this;
    }
}
