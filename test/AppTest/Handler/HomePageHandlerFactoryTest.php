<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class HomePageHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testFactoryWithoutTemplate(): void
    {
        $factory = new HomePageHandlerFactory();
        $this->container
            ->method('has')
            ->with(TemplateRendererInterface::class)
            ->willReturn(false);

        $this->container
            ->method('get')
            ->with(RouterInterface::class)
            ->willReturn($this->createMock(RouterInterface::class));

        $homePage = $factory($this->container);

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }

    public function testFactoryWithTemplate(): void
    {
        $this->container
            ->method('has')
            ->with(TemplateRendererInterface::class)
            ->willReturn(true);

        $this->container
            ->method('get')
            ->withConsecutive(
                [RouterInterface::class],
                [TemplateRendererInterface::class]
            )
            ->willReturnOnConsecutiveCalls(
                $this->createMock(RouterInterface::class),
                $this->createMock(TemplateRendererInterface::class)
            );

        $factory = new HomePageHandlerFactory();

        $homePage = $factory($this->container);

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }
}
