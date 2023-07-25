#!/usr/bin/env php
<?php

use Bref\Context\Context;
use Bref\Runtime\LambdaRuntime;
use Dayspring\LambdaBundle\Service\LambdaHandlerServiceInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Dotenv\Dotenv;

// memory to save for system overhead (in MB)
const DEFAULT_RESERVED_MEMORY_SIZE = 96;

set_time_limit(0);

$appRoot = getenv('LAMBDA_TASK_ROOT');

/** @noinspection PhpIncludeInspection */
require $appRoot . '/vendor/autoload.php';

$lambdaRuntime = LambdaRuntime::fromEnvironmentVariable('console');

(new Dotenv())->load($appRoot.'/.env.local');

$env = getenv('SYMFONY_ENV') ?: 'dev';
$debug = getenv('SYMFONY_DEBUG') !== '0' && $env !== 'prod';
$handlerService = getenv('_HANDLER') ?: 'Dayspring\LambdaBundle\Service\EchoLambdaHandlerService';

$lambdaMemorySize = getenv('AWS_LAMBDA_FUNCTION_MEMORY_SIZE');
if ($lambdaMemorySize) {
    if (!is_numeric($lambdaMemorySize)) {
        die(sprintf("AWS_LAMBDA_FUNCTION_MEMORY_SIZE expected to be numeric, '%s' given\n", $lambdaMemorySize));
    }

    $reservedMemorySize = getenv('RESERVED_MEMORY_SIZE') ?: DEFAULT_RESERVED_MEMORY_SIZE;
    if (!is_numeric($reservedMemorySize)) {
        printf("RESERVED_MEMORY_SIZE expected to be numeric, '%s' given; using default.\n", $reservedMemorySize);
        $reservedMemorySize = DEFAULT_RESERVED_MEMORY_SIZE;
    }

    $phpMemoryLimit = (int)$lambdaMemorySize - $reservedMemorySize;
    printf("Configured memory: %d MB; setting memory_limit to %d MB\n", $lambdaMemorySize, $phpMemoryLimit);
    ini_set('memory_limit', sprintf("%dM", $phpMemoryLimit));
}


if ($debug) {
    Debug::enable();
}

/** @var LambdaHandlerServiceInterface $service */
$service = null;
try {
    $kernel = new AppKernel($env, $debug);
    $kernel->boot();

    $service = $kernel->getContainer()->get($handlerService);

    $service->init();
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

        // calculate peak memory usage and output
        $peakMemoryUsage = memory_get_peak_usage();
        $peakMemoryUsageMB = $peakMemoryUsage / 1024 / 1024;
        printf("PHP peak memory usage: %d MB\n", $peakMemoryUsageMB);

        return $returnValue;
    });
}
