<?php

namespace App\UseCases;

use App\Metrics\ICollector;
use App\Repositories\IAccessLogRepository;

final class FillerRequestMetrics
{
    public function __construct(
        private IAccessLogRepository $accessLogRepository,
    ) {}

    public function byLogs(ICollector $collector): ICollector
    {
        foreach ($this->accessLogRepository->getChunksGenerator(5) as $chunk) {
            /** @var \App\Entities\AccessLog $log */
            foreach ($chunk as $log) {
                $collector
                    ->getCounter()
                    ->inc($log->getCreatedAt())
                ;
                $collector
                    ->getHistogram()
                    ->set(
                        $log->getCreatedAt(),
                        $log->getTimeMilliseconds(),
                        [
                            $log->getStatus(),
                        ]
                    )
                ;
            }
        }

        return $collector;
    }
}
