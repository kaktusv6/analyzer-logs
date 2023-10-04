#!/usr/bin/env php
<?php

include_once './bootstrap.php';

use App\Commands\ServiceUnavailableServiceLogsAnalyzeCommand;
use App\Commands\ServiceUnavailableServiceLogsAnalyzeGenerateCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$container = (new \App\Container\ContainerInitializer())->init();

$application
    ->addCommands([
        $container->get(ServiceUnavailableServiceLogsAnalyzeGenerateCommand::class),
        $container->get(ServiceUnavailableServiceLogsAnalyzeCommand::class),
    ]);

$application->run();
