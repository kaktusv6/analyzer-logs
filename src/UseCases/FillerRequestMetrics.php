<?php

namespace App\UseCases;

use App\Entities\AccessLog;
use App\Metrics\ICollector;
use App\Repositories\IAccessLogRepository;

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
}
