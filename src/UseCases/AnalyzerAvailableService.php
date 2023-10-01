<?php

namespace App\UseCases;

use App\Entities\AnalysisRequestAvailableData;
use Carbon\Carbon;

final class AnalyzerAvailableService
{
    public function __construct(
        private GetterRequestMetrics $getterMetrics,
    ) {}

    /** @return array<int, AnalysisRequestAvailableData> */
    public function analyze(float $precentAvailable = 90): array
    {
        $metrics = $this->getterMetrics->get();

        $counter = $metrics->getCounter();
        $histogram = $metrics->getHistogram();

        [$fast, $long] = $histogram->getCounters();

        $result = [];

        foreach ($counter->get() as $time => $requestCount) {
            $fastAndSuccessRequestCount = $fast->getByTime($time, [200]);

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
