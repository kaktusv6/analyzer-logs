<?php

namespace App\UseCases;

use App\Entities\Factories\AccessLogFactory;
use App\Entities\UnavailableServiceIntervalInfo;
use App\Entities\UnavailableServiceTimeInfo;
use App\Metrics\ICollector;
use App\Repositories\AccessLogResourceRepository;
use App\Utils\Files\Reader;
use Carbon\Carbon;

final class AnalyzerUnavailableService
{
    public function __construct(
        private ICollector $metricsCollector,
        private CreatorIntervals $creatorIntervals,
    ) {}

    /** @return UnavailableServiceIntervalInfo[] */
    public function analyze(float $precent, float $time, string $path): array
    {
        $counter = $this->metricsCollector->createCounter('request_counter');
        $histogram = $this->metricsCollector->createHistogram('response_times_milliseconds', [$time], ['status']);

        $this->createFillerRequestMetrics($path)
            ->byLogs($this->metricsCollector)
        ;

        [$fastRequestsCounter] = $histogram->getCounters();

        $mapTimeToUnavailableServiceInfo = [];

        foreach ($counter->get() as $time => $requestCount) {
            $fastAndSuccessRequestCount = $fastRequestsCounter->getByTime($time, [200]);

            $successRequestCount = $fastAndSuccessRequestCount;

            $present = ($successRequestCount / $requestCount) * 100;

            if ($present < $precent) {
                $mapTimeToUnavailableServiceInfo[$time] = (new UnavailableServiceTimeInfo())
                    ->setCount($requestCount)
                    ->setPresent($present)
                    ->setSuccessCount($successRequestCount)
                    ->setTime(Carbon::createFromTimestamp($time))
                ;
            }
        }

        $intervals = $this->creatorIntervals->byPoints(array_keys($mapTimeToUnavailableServiceInfo));

        /** @var UnavailableServiceIntervalInfo[] $result */
        $result = [];
        foreach ($intervals as $interval) {
            if (!$interval->isToOnePoint()) {
                $startIntervalData = $mapTimeToUnavailableServiceInfo[$interval->getStart()];
                $endIntervalData = $mapTimeToUnavailableServiceInfo[$interval->getEnd()];

                $count = $endIntervalData->getCount() - $startIntervalData->getCount() + 1;
                $successCount = $endIntervalData->getSuccessCount() - $startIntervalData->getSuccessCount();

                $present = ($successCount / $count) * 100;

                $result[] = (new UnavailableServiceIntervalInfo())
                    ->setPresent($present)
                    ->setStartedAt(Carbon::createFromTimestamp($interval->getStart()))
                    ->setEndedAt(Carbon::createFromTimestamp($interval->getEnd()))
                ;
            }
        }

        return $result;
    }

    private function createFillerRequestMetrics(string $path): FillerRequestMetrics
    {
        $repository = new AccessLogResourceRepository(
            new Reader($path),
            new AccessLogFactory(),
        );

        return new FillerRequestMetrics(
            $repository,
        );
    }
}
