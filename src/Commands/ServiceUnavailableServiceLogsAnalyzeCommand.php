<?php

namespace App\Commands;

use App\UseCases\AnalyzerUnavailableService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'analyze:unavailable:logs',
    description: 'Команда читает логи запросов к сервису, анализирует и выводит временные интервалы в которых доля отказов была ниже указанной',
)]
final class ServiceUnavailableServiceLogsAnalyzeCommand extends Command
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

        $result = $this->analyzer->analyze($present, $time, $path);

        foreach ($result as $info) {
            $output->writeln((string)$info);
        }

        return Command::SUCCESS;
    }
}
