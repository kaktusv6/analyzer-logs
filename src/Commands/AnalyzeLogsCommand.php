<?php

namespace App\Commands;

use App\Metrics\ICollector;
use App\Repositories\IAccessLogRepository;
use App\UseCases\AnalyzerAvailableService;
use App\UseCases\CreatorIntervals;
use App\UseCases\FillerRequestMetrics;
use App\ValueObjects\Interval;
use Carbon\Carbon;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'analyze:logs',
    description: 'Команда читает логи запросов к сервису, анализирует и выводит временные интервалы в которых доля отказов была ниже указанной',
)]
final class AnalyzeLogsCommand extends Command
{
    public function __construct(
        private ICollector $collector,
        private IAccessLogRepository $accessLogRepository,
        private CreatorIntervals $creatorIntervals,
        private AnalyzerAvailableService $analyzer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('present', InputArgument::REQUIRED, 'Минимально допустимый уровень доступности');
        $this->addArgument('allow_time', InputArgument::REQUIRED, 'Приемлемое время ответа сервиса в миллисекундах');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $present = (float) $input->getArgument('present');
        $allowTiming = (float) $input->getArgument('allow_time');

        $this->collector->createCounter('request_counter');
        $this->collector->createHistogram('response_times_milliseconds', [$allowTiming], ['status']);

        $getterMetrics = new FillerRequestMetrics(
            $this->accessLogRepository,
        );

        $metricsCollector = $getterMetrics->byLogs($this->collector);

        $mapTimeToAnalyzeData = $this->analyzer->analyze(
            $metricsCollector,
            $present,
        );

        $intervals = $this->creatorIntervals->byPoints(array_keys($mapTimeToAnalyzeData));

        foreach ($intervals as $interval) {
            if (!$interval->isToOnePoint()) {
                $startIntervalData = $mapTimeToAnalyzeData[$interval->getStart()];
                $endIntervalData = $mapTimeToAnalyzeData[$interval->getEnd()];

                $count = $endIntervalData->getCount() - $startIntervalData->getCount() + 1;
                $successCount = $endIntervalData->getSuccessCount() - $startIntervalData->getSuccessCount();

                $present = ($successCount / $count) * 100;

                $output->writeln(sprintf(
                    '%s %s %01.2f',
                    Carbon::createFromTimestamp($interval->getStart()),
                    Carbon::createFromTimestamp($interval->getEnd()),
                    $present
                ));
            }
        }

        return Command::SUCCESS;
    }
}
