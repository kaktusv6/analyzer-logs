<?php

namespace App\Commands;

use App\Metrics\ICollector;
use App\Repositories\IAccessLogRepository;
use App\UseCases\AnalyzerAvailableService;
use App\UseCases\GetterRequestMetrics;
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
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('precent', InputArgument::REQUIRED, 'Минимально допустимый уровень доступности');
        $this->addArgument('allow_time', InputArgument::REQUIRED, 'Приемлемое время ответа червиса в милисекундах');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $precent = (float) $input->getArgument('precent');
        $allowTiming = (float) $input->getArgument('allow_time');

        $this->collector->createCounter('request_counter');
        $this->collector->createHistogram('response_times_milliseconds', [$allowTiming], ['status']);

        $getterMetrics = new GetterRequestMetrics(
            $this->accessLogRepository,
            $this->collector
        );
        $metricsCollector = $getterMetrics->get();

        $analyzer = new AnalyzerAvailableService();

        $mapTimeToAnalyzeData = $analyzer->analyze(
            $metricsCollector,
            $precent,
        );

        // TODO вынести создание интервалов в UseCase
        /** @var Interval[] $intervals */
        $intervals = [];
        $start = array_key_first($mapTimeToAnalyzeData);
        $end = $start;
        foreach ($mapTimeToAnalyzeData as $time => $_) {
            if ($time - $end <= 1) {
                $end = $time;
            } else {
                $intervals[] = new Interval($start, $end);
                $start = $time;
                $end = $time;
            }
        }

        if (null !== $start) {
            $intervals[] = new Interval($start, $end);
        }

        foreach ($intervals as $interval) {
            if (!$interval->isToOnePoint()) {
                $startIntervalData = $mapTimeToAnalyzeData[$interval->getStart()];
                $endIntervalData = $mapTimeToAnalyzeData[$interval->getEnd()];

                $count = $endIntervalData->getCount() - $startIntervalData->getCount() + 1;
                $successCount = $endIntervalData->getSuccessCount() - $startIntervalData->getSuccessCount();

                $precent = ($successCount / $count) * 100;

                $output->writeln(sprintf(
                    '%s %s %01.2f',
                    Carbon::createFromTimestamp($interval->getStart()),
                    Carbon::createFromTimestamp($interval->getEnd()),
                    $precent
                ));
            }
        }

        return Command::SUCCESS;
    }
}
