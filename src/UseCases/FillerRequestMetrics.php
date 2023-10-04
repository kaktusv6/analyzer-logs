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

    public function getMetricsByGenerator(float $time): \Generator
    {
        $requestsInfo = null;

        foreach ($this->accessLogRepository->getChunksGenerator() as $chunk) {
            /** @var AccessLog $log */
            foreach ($chunk as $log) {
                if ($requestsInfo === null) {
                    $rpc = [
                        'count' => 1,
                    ];
                    $bucketKey = $log->getTimeMilliseconds() <= $time ? $time : PHP_INT_MAX;
                    $rpc[$bucketKey][$log->getStatus()] = 1;

                    $requestsInfo = [
                        'time' => $log->getCreatedAt()->unix(),
                        'rpc' => $rpc,
                    ];
                }
                if ($requestsInfo['time'] !== $log->getCreatedAt()->unix()) {
                    yield $requestsInfo;
                    $rpc = [
                        'count' => 1,
                    ];
                    $bucketKey = $log->getTimeMilliseconds() <= $time ? $time : PHP_INT_MAX;
                    $rpc[$bucketKey][$log->getStatus()] = 1;

                    $requestsInfo = [
                        'time' => $log->getCreatedAt()->unix(),
                        'rpc' => $rpc,
                    ];
                } else {
                    $requestsInfo['rpc']['count']++;

                    $bucketKey = $log->getTimeMilliseconds() <= $time ? $time : PHP_INT_MAX;

                    $bucket = $requestsInfo['rpc'][$bucketKey] ?? [];

                    if (array_key_exists($log->getStatus(), $bucket)) {
                        $bucket[$log->getStatus()]++;
                    } else {
                        $bucket[$log->getStatus()] = 1;
                    }

                    $requestsInfo['rpc'][$bucketKey] = $bucket;
                }
            }
        }
    }
}
