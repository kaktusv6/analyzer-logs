<?php

use App\Entities\Factories\AccessLogFactory;
use App\Repositories\AccessLogResourceRepository;
use App\Utils\Files\Reader;
use App\ValueObjects\Interval;
use Carbon\Carbon;

include_once './vendor/autoload.php';

(new \App\Fakers\AccessLogFaker())->run();

$repository = new AccessLogResourceRepository(
    //    new Reader('php://stdin')
    new Reader(__DIR__.'/resources/logs/access.example.2.log'),
    new AccessLogFactory()
);

$allowTiming = 45;
$precent = 90;

$collector = new \App\Metrics\CollectorInMemory();
$collector->createCounter('request_counter');
$collector->createHistogram('response_times_milliseconds', [$allowTiming], ['status']);

$getterMetrics = new \App\UseCases\GetterRequestMetrics(
    $repository,
    $collector
);

$analyzer = new \App\UseCases\AnalyzerAvailableService(
    $getterMetrics,
);

$timesLowAvailable = $analyzer->analyze($precent);

$intervals = [];
$start = array_key_first($timesLowAvailable);
$end = $start;
foreach ($timesLowAvailable as $time => $_) {
    if ($time - $end <= 1) {
        $end = $time;
    } else {
        $intervals[] = new Interval($start, $end);
        $start = $time;
        $end = $time;
    }
}

if (count($intervals) > 0) {
    $intervals[] = new Interval($start, $end);
}

foreach ($intervals as $interval) {
    if (!$interval->isToOnePoint()) {
        $startIntervalData = $timesLowAvailable[$interval->getStart()];
        $endIntervalData = $timesLowAvailable[$interval->getEnd()];

        $count = $endIntervalData->getCount() - $startIntervalData->getCount() + 1;
        $successCount = $endIntervalData->getSuccessCount() - $startIntervalData->getSuccessCount();

        $precent = ($successCount / $count) * 100;

        echo sprintf(
            "%s %s %01.2f\n",
            Carbon::createFromTimestamp($interval->getStart()),
            Carbon::createFromTimestamp($interval->getEnd()),
            $precent
        );
    }
}
