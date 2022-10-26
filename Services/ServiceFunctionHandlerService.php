<?php

namespace Dayspring\LambdaBundle\Service;

use Bref\Context\Context;
use Dayspring\LambdaBundle\Services\LambdaHandlerServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function json_decode;
use function json_encode;
use function var_dump;
use function var_export;

class ServiceFunctionHandlerService implements LambdaHandlerServiceInterface
{

    /** @var LoggerInterface $logger */
    protected $logger;

    /** @var ContainerInterface $container */
    protected $container;


    /**
     * @param LoggerInterface $logger
     * @param ContainerInterface $container
     */
    public function __construct(
        LoggerInterface $logger,
        ContainerInterface $container
    ) {
        $this->logger = $logger;
        $this->container = $container;
    }


    public function handle($event, Context $context, OutputInterface $output)
    {
        $object = $this->container->get($event['serviceName']);

        $this->logger->info(sprintf(
            'SqsServiceFunctionHandlerService will run %s::%s with %s',
            $event['serviceName'],
            $event['function'],
            json_encode($event['args'])
        ));

        $callbackReturn = call_user_func_array([$object, $event['function']], $event['args']);
        $output->writeln(var_export($callbackReturn, true));

        return 0;
    }

}
