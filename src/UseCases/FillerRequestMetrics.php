<?php

namespace App\UseCases;

use App\Entities\AccessLog;
use App\Entities\Bucket;
use App\Entities\Histogram;
use App\Metrics\ICollector;
use App\Repositories\IAccessLogRepository;
use Carbon\Carbon;

final class FillerRequestMetrics
{
    public function __construct(
        private IAccessLogRepository $accessLogRepository,
    ) {
    }

    public function byLogs(ICollector $collector): ICollector
    {
        foreach ($this->accessLogRepository->getChunksGenerator() as $chunk) {
            /** @var AccessLog $log */
            foreach ($chunk as $log) {
                $collector
                    ->getCounter()
                    ->inc($log->getCreatedAt());
                $collector
                    ->getHistogram()
                    ->set(
                        $log->getCreatedAt(),
                        $log->getTimeMilliseconds(),
                        [
                            $log->getStatus(),
                        ]
                    );
            }
        }

        return $collector;
    }

    public function getHistogramFlow(float $time): \Generator
    {
        $histogram = null;
        foreach ($this->accessLogRepository->getChunksGenerator() as $chunk) {
            /** @var AccessLog $log */
            foreach ($chunk as $log) {
                if ($histogram === null) {
                    $histogram = $this->createHistogramFromLog($log, $time);
                    continue;
                }
                if ($histogram->getTime()->unix() !== $log->getCreatedAt()->unix()) {
                    yield $histogram;

                    $histogram = $this->createHistogramFromLog($log, $time);
                } else {
                    $histogram->fix($log->getTimeMilliseconds(), $log->getStatus());
                }
            }

        }
    }

    private function createHistogramFromLog(AccessLog $log, float $time): Histogram
    {
        $histogram = new Histogram($log->getCreatedAt(), [
            new Bucket($time, [200, 500]),
            new Bucket(PHP_INT_MAX, [200, 500]),
        ]);
        $histogram->fix($log->getTimeMilliseconds(), $log->getStatus());
        return $histogram;
    }
}
