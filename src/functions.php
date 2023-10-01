<?php

use App\Commands\AnalyzeLogsCommand;
use App\Entities\Factories\AccessLogFactory;
use App\Metrics\CollectorInMemory;
use App\Metrics\ICollector;
use App\Repositories\AccessLogResourceRepository;
use App\Repositories\IAccessLogRepository;
use App\Utils\Files\Reader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

if (!function_exists('create')) {
    function create(): Psr\Container\ContainerInterface
    {
        // add lazy
        // find enable global autowire
        $container = new ContainerBuilder();

        $container->register('reader.stdin', Reader::class)
//            ->addArgument('php://stdin')
            ->addArgument(__DIR__.'/../resources/logs/access.example.2.log')
        ;

        $container->register(AccessLogFactory::class, AccessLogFactory::class);

        $container->autowire(IAccessLogRepository::class, AccessLogResourceRepository::class)
            ->addArgument($container->get('reader.stdin'))
            ->addArgument($container->get(AccessLogFactory::class))
        ;

        $container->autowire(ICollector::class, CollectorInMemory::class);

        $container->register(AnalyzeLogsCommand::class, AnalyzeLogsCommand::class)
            ->addArgument($container->get(ICollector::class))
            ->addArgument($container->get(IAccessLogRepository::class))
        ;

        return $container;
    }
}
