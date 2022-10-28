#!/usr/bin/env php
<?php

use Bref\Context\Context;
use Bref\Runtime\LambdaRuntime;
use Dayspring\LambdaBundle\Services\LambdaHandlerServiceInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;

set_time_limit(0);

$appRoot = getenv('LAMBDA_TASK_ROOT');

/** @noinspection PhpIncludeInspection */
require $appRoot . '/vendor/autoload.php';

$lambdaRuntime = LambdaRuntime::fromEnvironmentVariable('console');

(new Dotenv())->load($appRoot.'/.env.local');

$env = getenv('SYMFONY_ENV') ?: 'dev';
$debug = getenv('SYMFONY_DEBUG') !== '0' && $env !== 'prod';
$handlerService = getenv('_HANDLER') ?: 'Dayspring\LambdaBundle\Services\EchoLambdaHandlerService';


if ($debug) {
    Debug::enable();
}

/** @var LambdaHandlerServiceInterface $service */
$service = null;
try {
    $kernel = new AppKernel($env, $debug);
    $kernel->boot();

    $service = $kernel->getContainer()->get($handlerService);
} catch (Exception $e) {
    // error getting service
    $lambdaRuntime->failInitialization(sprintf(
        "Error initializing function - %s: %s",
        get_class($e),
        $e->getMessage()
    ));
}

while (true) {
    $lambdaRuntime->processNextEvent(function ($event, Context $context) use ($service): array {

        $output = new BufferedOutput();

        $returnValue = $service->handle($event, $context, $output);

        $out = $output->fetch();
        echo $out;

        return $returnValue;
    });
}
