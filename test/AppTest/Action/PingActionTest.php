<?php

declare(strict_types=1);

namespace AppTest\Action;

use App\Action\PingAction;
use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class PingActionTest extends TestCase
{
    public function testResponse()
    {
        $pingAction = new PingAction();
        $response = $pingAction->process(
            $this->prophesize(ServerRequestInterface::class)->reveal(),
            $this->prophesize(RequestHandlerInterface::class)->reveal()
        );

        $json = json_decode((string) $response->getBody());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue(isset($json->ack));
    }
}
