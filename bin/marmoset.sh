#!/usr/bin/env php
<?php

namespace EAMann\Marmoset;

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();
$application->add( new Command\Command() );
$application->run();