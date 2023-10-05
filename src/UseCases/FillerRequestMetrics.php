<?php

namespace App\UseCases;

use App\Entities\AccessLog;
use App\Entities\Bucket;
use App\Entities\Histogram;
use App\Metrics\ICollector;
use App\Repositories\IAccessLogRepository;
use Carbon\Carbon;

/** UseCAse заполнения метрики по запросам */
final class FillerRequestMetrics
{
    public function __construct(
        private IAccessLogRepository $accessLogRepository,
    ) {
    }

    /**
     * Метод заполняет метрику по логам
     */
    public function byLogs(ICollector $collector): ICollector
    {
        foreach ($this->accessLogRepository->getChunksGenerator() as $chunk) {
            /** @var AccessLog $log */
            foreach ($chunk as $log) {
                // Фиксируем запрос общем счетчике запросов
                $collector
                    ->getCounter()
                    ->inc($log->getCreatedAt());
                // Фиксируем запрос в гистограмме по времени и статусу ответа
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
