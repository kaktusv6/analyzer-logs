<?php

namespace App\UseCases;

use App\Entities\AnalysisRequestAvailableData;
use App\Metrics\ICollector;
use Carbon\Carbon;

final class AnalyzerAvailableService
{
    /** @return array<int, AnalysisRequestAvailableData> */
    public function analyze(ICollector $metricsCollector, float $precentAvailable = 90): array
    {
        $counter = $metricsCollector->getCounter();
        $histogram = $metricsCollector->getHistogram();

        [$fastRequestsCounter] = $histogram->getCounters();

        $result = [];

        foreach ($counter->get() as $time => $requestCount) {
            $fastAndSuccessRequestCount = $fastRequestsCounter->getByTime($time, [200]);

            $successRequestCount = $fastAndSuccessRequestCount;

            $precent = ($successRequestCount / $requestCount) * 100;

            if ($precent < $precentAvailable) {
                $result[$time] = (new AnalysisRequestAvailableData())
                    ->setCount($requestCount)
                    ->setPrecent($precent)
                    ->setSuccessCount($successRequestCount)
                    ->setTime(Carbon::createFromTimestamp($time))
                ;
            }
        }

        return $result;
    }
}
