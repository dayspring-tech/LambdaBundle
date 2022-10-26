<?php

namespace Dayspring\LambdaBundle\Tests\Services;

class EchoLambdaHandlerServiceTest extends \PHPUnit\Framework\TestCase
{

    public function testHandle()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        /** @var \Dayspring\LambdaBundle\Services\EchoLambdaHandlerService $service */
        $service = new \Dayspring\LambdaBundle\Services\EchoLambdaHandlerService($logger);

        $context = new \Bref\Context\Context(
            'request-id-1',
            30*1000,
            'my-function-arn',
            'trace-id-1'
        );
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        $event = [
            'body' => 'hello world'
        ];
        $return = $service->handle($event, $context, $output);

        $this->assertEquals(0, $return);
        $this->assertEquals("hello world\n", $output->fetch());
    }

}
