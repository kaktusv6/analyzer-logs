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
    name: 'analyze:logs:unavailable:stream',
    description: 'Команда потоково считывает логи, анализирует недоступность сервиса и выводит интервалы недоступности сервиса',
)]
final class ServiceUnavailableLogsAnalyzeStreamCommand extends Command
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

        foreach ($this->analyzer->analyzeFlow($present, $time, $path) as $histogram) {
            $output->writeln((string)$histogram);
        }

        return Command::SUCCESS;
    }
}
