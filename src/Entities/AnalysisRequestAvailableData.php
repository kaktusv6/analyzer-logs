<?php

namespace App\Entities;

use Carbon\Carbon;

final class AnalysisRequestAvailableData
{
    private Carbon $time;
    private float $present;
    private int $count;
    private int $successCount;

    public function getPresent(): float
    {
        return $this->present;
    }

    public function setPresent(float $present): AnalysisRequestAvailableData
    {
        $this->present = $present;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): AnalysisRequestAvailableData
    {
        $this->count = $count;

        return $this;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function setSuccessCount(int $successCount): AnalysisRequestAvailableData
    {
        $this->successCount = $successCount;

        return $this;
    }

    public function getTime(): Carbon
    {
        return $this->time;
    }

    public function setTime(Carbon $time): AnalysisRequestAvailableData
    {
        $this->time = $time;

        return $this;
    }
}
