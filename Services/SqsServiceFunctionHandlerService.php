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

class SqsServiceFunctionHandlerService implements LambdaHandlerServiceInterface
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
        foreach ($event['Records'] as $record) {
            $data = json_decode($record['body'], true);
            $object = $this->container->get($data['serviceName']);

            $this->logger->info(sprintf(
                'SqsServiceFunctionHandlerService will run %s::%s with %s',
                $data['serviceName'],
                $data['function'],
                json_encode($data['args'])
            ));

            $callbackReturn = call_user_func_array([$object, $data['function']], $data['args']);
            $output->writeln(var_export($callbackReturn, true));
        }

        return 0;
    }

}
