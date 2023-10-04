<?php

namespace App\UseCases;

use App\Entities\AnalysisRequestAvailableData;
use App\Metrics\ICollector;
use Carbon\Carbon;

final class AnalyzerAvailableService
{
    /** @return array<int, AnalysisRequestAvailableData> */
    public function analyze(ICollector $metricsCollector, float $presentAvailable = 90): array
    {
        $counter = $metricsCollector->getCounter();
        $histogram = $metricsCollector->getHistogram();

        [$fastRequestsCounter] = $histogram->getCounters();

        $result = [];

        foreach ($counter->get() as $time => $requestCount) {
            $fastAndSuccessRequestCount = $fastRequestsCounter->getByTime($time, [200]);

            $successRequestCount = $fastAndSuccessRequestCount;

            $present = ($successRequestCount / $requestCount) * 100;

            if ($present < $presentAvailable) {
                $result[$time] = (new AnalysisRequestAvailableData())
                    ->setCount($requestCount)
                    ->setPresent($present)
                    ->setSuccessCount($successRequestCount)
                    ->setTime(Carbon::createFromTimestamp($time))
                ;
            }
        }

        return $result;
    }
}
