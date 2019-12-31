<?php

namespace App\Action;

use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class HomePageFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $router   = $container->get(RouterInterface::class);
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : ($container->has(\Zend\Expressive\Template\TemplateRendererInterface::class)
                ? $container->get(\Zend\Expressive\Template\TemplateRendererInterface::class)
                : null);

        return new HomePageAction($router, $template);
    }
}
