<?php

namespace AppTest\Action;

use App\Action\HomePageAction;
use App\Action\HomePageFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomePageFactoryTest extends TestCase
{
    /** @var ContainerInterface */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $router = $this->prophesize(RouterInterface::class);

        $this->container->get(RouterInterface::class)->willReturn($router);
    }

    public function testFactoryWithoutTemplate()
    {
        $factory = new HomePageFactory();
        $this->container->has(TemplateRendererInterface::class)->willReturn(false);

        $this->assertInstanceOf(HomePageFactory::class, $factory);

        $homePage = $factory($this->container->reveal());

        $this->assertInstanceOf(HomePageAction::class, $homePage);
    }

    public function testFactoryWithTemplate()
    {
        $factory = new HomePageFactory();
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $this->assertInstanceOf(HomePageFactory::class, $factory);

        $homePage = $factory($this->container->reveal());

        $this->assertInstanceOf(HomePageAction::class, $homePage);
    }
}
