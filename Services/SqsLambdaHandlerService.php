<?php

namespace Dayspring\LambdaBundle\Services;

use Bref\Context\Context;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SqsLambdaHandlerService implements LambdaHandlerServiceInterface
{

    /** @var LoggerInterface $logger */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle($event, Context $context, OutputInterface $output)
    {
        foreach ($event['Records'] as $e) {
            $output->writeln($e['body']);
        }

        return 0;
    }
}
