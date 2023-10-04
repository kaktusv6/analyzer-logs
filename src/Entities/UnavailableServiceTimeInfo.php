<?php

namespace App\Entities;

use Carbon\Carbon;

final class UnavailableServiceTimeInfo
{
    private Carbon $time;
    private float $present;
    private int $count;
    private int $successCount;

    public function getPresent(): float
    {
        return $this->present;
    }

    public function setPresent(float $present): UnavailableServiceTimeInfo
    {
        $this->present = $present;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): UnavailableServiceTimeInfo
    {
        $this->count = $count;

        return $this;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function setSuccessCount(int $successCount): UnavailableServiceTimeInfo
    {
        $this->successCount = $successCount;

        return $this;
    }

    public function getTime(): Carbon
    {
        return $this->time;
    }

    public function setTime(Carbon $time): UnavailableServiceTimeInfo
    {
        $this->time = $time;

        return $this;
    }
}
