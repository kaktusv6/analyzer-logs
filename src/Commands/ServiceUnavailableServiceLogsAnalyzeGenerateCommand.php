<?php

namespace App\Commands;

use App\Entities\Factories\AccessLogFactory;
use App\Repositories\AccessLogResourceRepository;
use App\UseCases\AnalyzerUnavailableService;
use App\UseCases\FillerRequestMetrics;
use App\Utils\Files\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'analyze:generate',
    description: 'Команда читает логи запросов к сервису, анализирует и выводит временные интервалы в которых доля отказов была ниже указанной',
)]
final class ServiceUnavailableServiceLogsAnalyzeGenerateCommand extends Command
{
    public function __construct(
        private AnalyzerUnavailableService $analyzer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('present', InputArgument::REQUIRED, 'Минимально допустимый уровень доступности');
        $this->addArgument('time', InputArgument::REQUIRED, 'Приемлемое время ответа сервиса в миллисекундах');
        $this->addArgument('path', InputArgument::OPTIONAL, 'Путь до файлов с логами. Если не указано, то логи будут считываться из stdin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $present = (float)$input->getArgument('present');
        $time = (float)$input->getArgument('time');
        $path = $input->getArgument('path') ?? 'php://stdin';

        $start = null;
        $end = null;
        foreach ($this->analyzer->analyzeGenerator($present, $time, $path) as $info) {
            if ($start === null) {
                $start = $info;
                $end = $info;
            } elseif ($end['time'] + 1 === $info['time']) {
                $end['time'] = $info['time'];
                $end['rpc']['count'] += $info['rpc']['count'];

                $successCount = ($end['rpc'][$time][200] ?? 0) + ($info['rpc'][$time][200] ?? 0);
                $end['rpc'][$time][200] = $successCount;
            } elseif ($start['time'] !== $end['time']) {
                $count = $end['rpc']['count'];
                $successCount = $end['rpc'][$time][200] ?? 0;

                $output->writeln(sprintf('%s %s %01.2f', $start['time'], $end['time'], ($successCount / $count) * 100));

                $start = $info;
                $end = $info;
            } else {
                $start = $info;
                $end = $info;
            }
        }

        return Command::SUCCESS;
    }
}
