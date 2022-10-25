#!/usr/bin/env php
<?php

use Bref\Context\Context;
use Bref\Runtime\LambdaRuntime;
use Dayspring\LambdaBundle\Services\LambdaHandlerServiceInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;

// if you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

set_time_limit(0);


$appRoot = getenv('LAMBDA_TASK_ROOT');

if (getenv('BREF_DOWNLOAD_VENDOR')) {
    if(! file_exists('/tmp/vendor') || ! file_exists('/tmp/vendor/autoload.php')) {
        require_once __DIR__ . '/breftoolbox.php';

        \Bref\ToolBox\BrefToolBox::downloadAndConfigureVendor();
    }

    require '/tmp/vendor/autoload.php';
} elseif (getenv('BREF_AUTOLOAD_PATH')) {
    /** @noinspection PhpIncludeInspection */
    require getenv('BREF_AUTOLOAD_PATH');
} else {
    /** @noinspection PhpIncludeInspection */
    require $appRoot . '/vendor/autoload.php';
}

$lambdaRuntime = LambdaRuntime::fromEnvironmentVariable('console');

//$handlerFile = $appRoot . '/' . getenv('_HANDLER');
//if (! is_file($handlerFile)) {
//    $lambdaRuntime->failInitialization("Handler `$handlerFile` doesn't exist");
//}

(new Dotenv())->load($appRoot.'/.env.local');

$env = getenv('SYMFONY_ENV') ?: 'dev';
$debug = getenv('SYMFONY_DEBUG') !== '0' && $env !== 'prod';
$handlerService = getenv('_HANDLER') ?: 'Dayspring\LambdaBundle\Services\EchoLambdaHandlerService';


if ($debug) {
    Debug::enable();
}

$kernel = new AppKernel($env, $debug);
$kernel->boot();

while (true) {
    $lambdaRuntime->processNextEvent(function ($event, Context $context) use ($handlerService, $kernel): array {

        $output = new BufferedOutput();

        /** @var LambdaHandlerServiceInterface $service */
        $service = $kernel->getContainer()->get($handlerService);
        $exitCode = $service->handle($event, $context, $output);

        $out = $output->fetch();
        echo $out;

        return [
            'exitCode' => $exitCode, // will always be 0
            'output' => $out,
        ];
    });
}
