<?php

declare(strict_types=1);

namespace App\Handler;

use Chubbyphp\Container\MinimalContainer;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Plates\PlatesRenderer;
use Mezzio\Router;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\TwigRenderer;
use Pimple\Psr11\Container as PimpleContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HomePageHandler implements RequestHandlerInterface
{
    /** @var string */
    private $containerName;

    /** @var Router\RouterInterface */
    private $router;

    /** @var null|TemplateRendererInterface */
    private $template;

    public function __construct(
        string $containerName,
        Router\RouterInterface $router,
        ?TemplateRendererInterface $template = null
    ) {
        $this->containerName = $containerName;
        $this->router        = $router;
        $this->template      = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];

        switch ($this->containerName) {
            case PimpleContainer::class:
                $data['containerName'] = 'Pimple';
                $data['containerDocs'] = 'https://pimple.symfony.com/';
                break;
            case ServiceManager::class:
                $data['containerName'] = 'Laminas Servicemanager';
                $data['containerDocs'] = 'https://docs.laminas.dev/laminas-servicemanager/';
                break;
            case ContainerBuilder::class:
                $data['containerName'] = 'Symfony DI Container';
                $data['containerDocs'] = 'https://symfony.com/doc/current/service_container.html';
                break;
            case MinimalContainer::class:
                $data['containerName'] = 'Chubbyphp Container';
                $data['containerDocs'] = 'https://github.com/chubbyphp/chubbyphp-container';
                break;
        }

        if ($this->router instanceof Router\FastRouteRouter) {
            $data['routerName'] = 'FastRoute';
            $data['routerDocs'] = 'https://github.com/nikic/FastRoute';
        } elseif ($this->router instanceof Router\LaminasRouter) {
            $data['routerName'] = 'Laminas Router';
            $data['routerDocs'] = 'https://docs.laminas.dev/laminas-router/';
        }

        if ($this->template === null) {
            return new JsonResponse([
                'welcome' => 'Congratulations! You have installed the mezzio skeleton application.',
                'docsUrl' => 'https://docs.mezzio.dev/mezzio/',
            ] + $data);
        }

        if ($this->template instanceof PlatesRenderer) {
            $data['templateName'] = 'Plates';
            $data['templateDocs'] = 'https://platesphp.com/';
        } elseif ($this->template instanceof TwigRenderer) {
            $data['templateName'] = 'Twig';
            $data['templateDocs'] = 'https://twig.symfony.com';
        } elseif ($this->template instanceof LaminasViewRenderer) {
            $data['templateName'] = 'Laminas View';
            $data['templateDocs'] = 'https://docs.laminas.dev/laminas-view/';
        }

        return new HtmlResponse($this->template->render('app::home-page', $data));
    }
}
