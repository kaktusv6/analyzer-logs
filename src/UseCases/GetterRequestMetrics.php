<?php

namespace App\UseCases;

use App\Metrics\ICollector;
use App\Repositories\IAccessLogRepository;

final class GetterRequestMetrics
{
    public function __construct(
        private IAccessLogRepository $accessLogRepository,
        private ICollector $metricsCollector,
    ) {}

    public function get(): ICollector
    {
        foreach ($this->accessLogRepository->getChunksGenerator(5) as $chunk) {
            /** @var \App\Entities\AccessLog $log */
            foreach ($chunk as $log) {
                $this->metricsCollector
                    ->getCounter()
                    ->inc($log->getCreatedAt())
                ;
                $this->metricsCollector
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

        return $this->metricsCollector;
    }
}
