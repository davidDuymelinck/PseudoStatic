<?php

require 'vendor/autoload.php';

use PseudoStatic\Command\AddPage;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new AddPage(__DIR__));

$application->run();