<?php

namespace AppTest\Action;

use App\Action\HomePageAction;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\RouterInterface;

class HomePageActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var RouterInterface */
    protected $router;

    protected function setUp()
    {
        $this->router = $this->prophesize(RouterInterface::class);
    }

    public function testResponse()
    {
        $homePage = new HomePageAction($this->router->reveal(), null);
        $response = $homePage(new ServerRequest(['/']), new Response(), function () {
        });

        $this->assertTrue($response instanceof Response);
    }
}
