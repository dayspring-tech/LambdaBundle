<?php

namespace Dayspring\LambdaBundle\Tests\Service;

use Bref\Context\Context;
use Dayspring\LambdaBundle\Service\EchoLambdaHandlerService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\BufferedOutput;

class EchoLambdaHandlerServiceTest extends TestCase
{

    public function testHandle()
    {
        $logger = $this->createMock(LoggerInterface::class);

        $service = new EchoLambdaHandlerService($logger);

        $context = new Context(
            'request-id-1',
            30*1000,
            'my-function-arn',
            'trace-id-1'
        );
        $output = new BufferedOutput();

        $event = [
            'body' => 'hello world'
        ];
        $return = $service->handle($event, $context, $output);

        $this->assertEquals([], $return);
        $this->assertEquals("hello world\n", $output->fetch());
    }

}
