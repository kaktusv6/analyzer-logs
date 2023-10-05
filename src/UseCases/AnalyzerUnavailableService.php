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
        private ICollector       $metricsCollector,
        private CreatorIntervals $creatorIntervals,
    ) {
    }

    /**
     * Метод анализирует метрики по логам и формирует временные интервалы в которые сервис был недоступен
     *
     * @depreceted Метод упирается в memory_limit
     *
     * @param float $present Приемлемый процент доступности сервиса
     * @param float $time Максимальное приемлемое время ответа запроса
     * @param string $path Путь до логов
     *
     * @return UnavailableServiceIntervalInfo[]
     */
    public function analyze(float $present, float $time, string $path): array
    {
        // Формируем коллекцию метрик
        $counter = $this->metricsCollector->createCounter('request_counter');
        $histogram = $this->metricsCollector->createHistogram('response_times_milliseconds', [$time], ['status']);

        // Заполняем метрики
        $this->createFillerRequestMetrics($path)
            ->byLogs($this->metricsCollector);


        // Из гистограммы нам нужен только бакет с успешно выполненными запросами которые удовлетворяют по времени ответа
        [$fastRequestsCounter] = $histogram->getCounters();

        // Формируем список моментов когда сервсис был недоступен
        $unavailableServiceTimeInfoList = [];
        foreach ($counter->get() as $time => $requestCount) {
            $fastAndSuccessRequestCount = $fastRequestsCounter->getByTime($time, [200]);

            $successRequestCount = $fastAndSuccessRequestCount;

            $presentAvailable = ($successRequestCount / $requestCount) * 100;

            if ($presentAvailable < $present) {
                $unavailableServiceTimeInfoList[] = (new UnavailableServiceTimeInfo())
                    ->setCount($requestCount)
                    ->setPresent($presentAvailable)
                    ->setSuccessCount($successRequestCount)
                    ->setTime(Carbon::createFromTimestamp($time));
            }
        }

        // Формируем временные интервалы на основании информации недоступности сервиса
        $points = array_map(fn (UnavailableServiceTimeInfo $info): int => $info->getTime()->unix(), $unavailableServiceTimeInfoList);
        $intervals = $this->creatorIntervals->byPoints($points);

        /** @var UnavailableServiceIntervalInfo[] $result */
        $result = [];
        foreach ($intervals as $interval) {
            if (!$interval->isToOnePoint()) {
                $startIntervalData = $unavailableServiceTimeInfoList[$interval->getStart()];
                $endIntervalData = $unavailableServiceTimeInfoList[$interval->getEnd()];

                $count = $endIntervalData->getCount() - $startIntervalData->getCount() + 1;
                $successCount = $endIntervalData->getSuccessCount() - $startIntervalData->getSuccessCount();

                $present = ($successCount / $count) * 100;

                $result[] = (new UnavailableServiceIntervalInfo())
                    ->setPresent($present)
                    ->setStartedAt(Carbon::createFromTimestamp($interval->getStart()))
                    ->setEndedAt(Carbon::createFromTimestamp($interval->getEnd()));
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

    /**
     * Метод потоково анализирует метрики по логам и формирует временные интервалы в которые сервис был недоступен
     *
     * @param float $present Приемлемый процент доступности сервиса
     * @param float $time Максимальное приемлемое время ответа запроса
     * @param string $path Путь до логов
     */
    public function analyzeFlow(float $present, float $time, string $path): \Generator
    {
        $filler = $this->createFillerRequestMetrics($path);

        $start = null;
        $end = null;

        // Потоково получаем гистограмму на момент времени
        foreach ($filler->getHistogramFlow($time) as $histogram) {
            // Вычисляем процент доступности
            $presentAvailable = ($histogram->getBucket($time)->getCounter(200)->getValue() / $histogram->getCount()) * 100;
            if ($presentAvailable < $present) {
                // Начинаем формировать интервал когда сервис был недоступен
                if ($start === null) {
                    $start = $histogram;
                    $end = $histogram;
                } elseif ($end->getTime()->diffInSeconds($histogram->getTime()) === 1) {
                    // Складываем прошлые значения гистограммы с текущей если разница по времени была в секунду
                    $histogram->setCount($end->getCount());
                    $histogram->getBucket($time)->getCounter(200)
                        ->inc(
                            $end->getBucket($time)->getCounter(200)->getValue()
                        );

                    $end = $histogram;
                } elseif ($start->getTime()->diffInSeconds($end->getTime()) > 0) {
                    // Если следующая гистограмма по времени больше по времени, значит текущий интервал закончился
                    yield (new UnavailableServiceIntervalInfo())
                        ->setPresent($end->getBucket($time)->getCounter(200)->getValue() / $end->getCount() * 100)
                        ->setStartedAt($start->getTime())
                        ->setEndedAt($end->getTime());

                    $start = $histogram;
                    $end = $histogram;
                } else {
                    $start = $histogram;
                    $end = $histogram;
                }
            }
        }
    }
}
