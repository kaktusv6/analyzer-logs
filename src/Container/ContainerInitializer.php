<?php

namespace App\Container;

use App\Commands\AnalyzeLogsCommand;
use App\Metrics\CollectorInMemory;
use App\Metrics\ICollector;
use App\UseCases\AnalyzerUnavailableService;
use App\UseCases\CreatorIntervals;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ContainerInitializer
{
    private static ?ContainerInterface $container = null;

    public function init(): ContainerInterface
    {
        if (null === self::$container) {
            self::$container = $this->build();
        }

        return self::$container;
    }

    private function build(): ContainerInterface
    {
        $container = new ContainerBuilder();

        $container->autowire(ICollector::class, CollectorInMemory::class);

        $container->register(CreatorIntervals::class, CreatorIntervals::class);

        $container->register(AnalyzerUnavailableService::class, AnalyzerUnavailableService::class)
            ->addArgument($container->get(ICollector::class))
            ->addArgument($container->get(CreatorIntervals::class))
        ;

        $container->register(AnalyzeLogsCommand::class, AnalyzeLogsCommand::class)
            ->addArgument($container->get(AnalyzerUnavailableService::class))
        ;

        return $container;
    }
}
