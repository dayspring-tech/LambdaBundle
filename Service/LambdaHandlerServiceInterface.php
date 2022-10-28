<?php

namespace Dayspring\LambdaBundle\Service;

use Bref\Context\Context;
use Symfony\Component\Console\Output\OutputInterface;

interface LambdaHandlerServiceInterface
{
    public function handle($event, Context $context, OutputInterface $output): array;
}
