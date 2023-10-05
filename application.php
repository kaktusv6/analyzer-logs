#!/usr/bin/env php
<?php

include_once './bootstrap.php';

use App\Commands\ServiceUnavailableLogsAnalyzeCommand;
use App\Commands\ServiceUnavailableLogsAnalyzeStreamCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$container = (new \App\Container\ContainerInitializer())->init();

$application
    ->addCommands([
        $container->get(ServiceUnavailableLogsAnalyzeStreamCommand::class),
        $container->get(ServiceUnavailableLogsAnalyzeCommand::class),
    ]);

$application->run();
