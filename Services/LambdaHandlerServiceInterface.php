<?php

namespace Dayspring\LambdaBundle\Services;

use Bref\Context\Context;
use Symfony\Component\Console\Output\OutputInterface;

interface LambdaHandlerServiceInterface
{
    public function handle($event, Context $context, OutputInterface $output);
}
