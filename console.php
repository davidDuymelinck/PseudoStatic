<?php

require 'vendor/autoload.php';

use PseudoStatic\Command\AddPage;
use PseudoStatic\Command\BuildSite;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new AddPage(__DIR__));
$application->add(new BuildSite(__DIR__));

$application->run();