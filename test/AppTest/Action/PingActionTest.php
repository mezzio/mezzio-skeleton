<?php

namespace AppTest\Action;

use App\Action\PingAction;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class PingActionTest extends TestCase
{
    public function testResponse()
    {
        $pingAction = new PingAction();
        $response = $pingAction->process(
            $this->prophesize(ServerRequestInterface::class)->reveal(),
            $this->prophesize(DelegateInterface::class)->reveal()
        );

        $json = json_decode((string) $response->getBody());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue(isset($json->ack));
    }
}
