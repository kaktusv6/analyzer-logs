<?php

namespace App\Entities;

use Carbon\Carbon;

final class AccessLog
{
    private Carbon $createdAt;
    private int $status;
    private float $timeMilliseconds;

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function setCreatedAt(Carbon $createdAt): AccessLog
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): AccessLog
    {
        $this->status = $status;

        return $this;
    }

    public function getTimeMilliseconds(): float
    {
        return $this->timeMilliseconds;
    }

    public function setTimeMilliseconds(float $timeMilliseconds): AccessLog
    {
        $this->timeMilliseconds = $timeMilliseconds;

        return $this;
    }
}
