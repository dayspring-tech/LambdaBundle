<?php

namespace Dayspring\LambdaBundle\Service;

use Bref\Context\Context;
use Symfony\Component\Console\Output\OutputInterface;

class SqsServiceFunctionHandlerService extends ServiceFunctionHandlerService
{
    public function init()
    {
        // nothing to init
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
