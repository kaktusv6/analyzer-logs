<?php

namespace App\UseCases;

use App\Entities\AccessLog;
use App\Entities\Bucket;
use App\Entities\Histogram;
use App\Repositories\IAccessLogRepository;

/** UseCAse получения гистограмм из логов */
final class GetterHistogramFromLogs
{
    public function __construct(
        private IAccessLogRepository $accessLogRepository,
    ) {
    }

    /** Метод потоково возвращает гистограмму запросов на момент времени */
    public function getFlow(float $time): \Generator
    {
        $histogram = null;
        foreach ($this->accessLogRepository->getChunksGenerator() as $chunk) {
            foreach ($chunk as $log) {
                if ($histogram === null) {
                    $histogram = $this->createHistogramFromLog($log, $time);
                    continue;
                }
                if ($histogram->getTime()->diffInSeconds($log->getCreatedAt()) === 0) {
                    // Фиксируем запрос в гистограмме если время совпадает
                    $histogram->fix($log->getTimeMilliseconds(), $log->getStatus());
                } else {
                    // Если момент времени лога не совпадает с временем гистограммы запросов, то возвращаем ее и формируем новую гистограмму на момент времени лога
                    yield $histogram;

                    $histogram = $this->createHistogramFromLog($log, $time);
                }
            }

        }
    }

    /** Метод создания гистограммы на основе лога. Сразу фиксируем в гистограмме запрос лога */
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
