<?php

namespace App\Entities\Factories;

use App\Entities\AccessLog;
use Carbon\Carbon;

final class AccessLogFactory
{
    public function createFromString(string $logStr): AccessLog
    {
        $re = '/([\d\.]*) - - \[(\d{2}\/\d{2}\/\d{4}:\d{2}:\d{2}:\d{2}) ([\+|\-]\d{4})\] (\"[^.].+\") (\d{3}) (\d) (\d+\.\d{6})/';

        preg_match(
            $re,
            $logStr,
            $matches,
            PREG_UNMATCHED_AS_NULL
        );

        [$_, $ip, $createdAt, $timezone, $request, $status, $number, $responseTime] = $matches;

        return (new AccessLog())
            ->setCreatedAt(Carbon::createFromFormat('d/m/Y:H:i:s', $createdAt, $timezone))
            ->setStatus($status)
            ->setTimeMilliseconds((float) $responseTime)
        ;
    }
}
