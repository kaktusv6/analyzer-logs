#!/usr/bin/env php
<?php

include_once './bootstrap.php';

use App\Commands\ServiceUnavailableServiceLogsAnalyzeCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$container = (new \App\Container\ContainerInitializer())->init();

$application
    ->add($container->get(ServiceUnavailableServiceLogsAnalyzeCommand::class));

$application->run();
