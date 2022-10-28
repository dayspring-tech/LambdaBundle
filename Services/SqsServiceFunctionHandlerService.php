<?php

namespace Dayspring\LambdaBundle\Service;

use Bref\Context\Context;
use Dayspring\LambdaBundle\Services\LambdaHandlerServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function is_a;
use function is_array;
use function json_decode;
use function json_encode;
use function var_dump;
use function var_export;

class SqsServiceFunctionHandlerService extends ServiceFunctionHandlerService
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


    public function handle($event, Context $context, OutputInterface $output): array
    {
        $callbackReturn = [];
        foreach ($event['Records'] as $record) {
            $data = json_decode($record['body'], true);

            $iterationReturn = parent::handle($data, $context, $output);
            $callbackReturn[] = $iterationReturn && is_array($iterationReturn) ? $iterationReturn : [];
        }

        return $callbackReturn;
    }

}
