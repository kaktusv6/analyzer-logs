#!/usr/bin/env php
<?php

require './vendor/autoload.php';

require './src/functions.php';

use App\Commands\AnalyzeLogsCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$container = create();

$application
    ->add($container->get(AnalyzeLogsCommand::class))
;

$application->run();
