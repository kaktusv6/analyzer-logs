<?php

namespace App\Container;

use App\Commands\AnalyzeLogsCommand;
use App\Entities\Factories\AccessLogFactory;
use App\Metrics\CollectorInMemory;
use App\Metrics\ICollector;
use App\Repositories\AccessLogResourceRepository;
use App\Repositories\IAccessLogRepository;
use App\UseCases\AnalyzerAvailableService;
use App\UseCases\CreatorIntervals;
use App\Utils\Files\Reader;
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

        $container->register('reader.stdin', Reader::class)
//            ->addArgument('php://stdin')
            ->addArgument(__DIR__.'/../../resources/logs/access.log')
        ;

        $container->register(AccessLogFactory::class, AccessLogFactory::class);

        $container->autowire(IAccessLogRepository::class, AccessLogResourceRepository::class)
            ->addArgument($container->get('reader.stdin'))
            ->addArgument($container->get(AccessLogFactory::class))
        ;

        $container->autowire(ICollector::class, CollectorInMemory::class);

        $container->register(CreatorIntervals::class, CreatorIntervals::class);
        $container->register(AnalyzerAvailableService::class, AnalyzerAvailableService::class);

        $container->register(AnalyzeLogsCommand::class, AnalyzeLogsCommand::class)
            ->addArgument($container->get(ICollector::class))
            ->addArgument($container->get(IAccessLogRepository::class))
            ->addArgument($container->get(CreatorIntervals::class))
            ->addArgument($container->get(AnalyzerAvailableService::class))
        ;

        return $container;
    }
}
