<?php

namespace App\Entities;

use Carbon\Carbon;

final class AnalysisRequestAvailableData
{
    private Carbon $time;
    private float $precent;
    private int $count;
    private int $successCount;

    public function getPrecent(): float
    {
        return $this->precent;
    }

    public function setPrecent(float $precent): AnalysisRequestAvailableData
    {
        $this->precent = $precent;

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
