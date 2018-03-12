<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Plates\PlatesRenderer;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Expressive\Twig\TwigRenderer;
use Zend\Expressive\ZendView\ZendViewRenderer;

class HomePageHandler implements RequestHandlerInterface
{
    private $containerName;

    private $router;

    private $template;

    public function __construct(
        string $containerName,
        Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null
    ){
        $this->containerName = $containerName;
        $this->router        = $router;
        $this->template      = $template;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if (! $this->template) {
            return new JsonResponse([
                'welcome' => 'Congratulations! You have installed the zend-expressive skeleton application.',
                'docsUrl' => 'https://docs.zendframework.com/zend-expressive/',
            ]);
        }

        $data = [];

        if ('Aura\Di\Container' == $this->containerName) {
            $data['containerName'] = 'Aura.Di';
            $data['containerDocs'] = 'http://auraphp.com/packages/2.x/Di.html';
        } elseif ('Pimple\Container' == $this->containerName) {
            $data['containerName'] = 'Pimple';
            $data['containerDocs'] = 'https://pimple.symfony.com/';
        } elseif ('Zend\ServiceManager\ServiceManager' == $this->containerName) {
            $data['containerName'] = 'Zend Servicemanager';
            $data['containerDocs'] = 'https://docs.zendframework.com/zend-servicemanager/';
        } elseif ('Auryn\Injector' == $this->containerName) {
            $data['containerName'] = 'Auryn';
            $data['containerDocs'] = 'https://github.com/rdlowrey/Auryn';
        } elseif ('Symfony\Component\DependencyInjection\ContainerBuilder' == $this->containerName) {
            $data['containerName'] = 'Symfony DI Container';
            $data['containerDocs'] = 'https://symfony.com/doc/current/service_container.html';
        }

        if ($this->router instanceof Router\AuraRouter) {
            $data['routerName'] = 'Aura.Router';
            $data['routerDocs'] = 'http://auraphp.com/packages/2.x/Router.html';
        } elseif ($this->router instanceof Router\FastRouteRouter) {
            $data['routerName'] = 'FastRoute';
            $data['routerDocs'] = 'https://github.com/nikic/FastRoute';
        } elseif ($this->router instanceof Router\ZendRouter) {
            $data['routerName'] = 'Zend Router';
            $data['routerDocs'] = 'https://docs.zendframework.com/zend-router/';
        }

        if ($this->template instanceof PlatesRenderer) {
            $data['templateName'] = 'Plates';
            $data['templateDocs'] = 'http://platesphp.com/';
        } elseif ($this->template instanceof TwigRenderer) {
            $data['templateName'] = 'Twig';
            $data['templateDocs'] = 'http://twig.sensiolabs.org/documentation';
        } elseif ($this->template instanceof ZendViewRenderer) {
            $data['templateName'] = 'Zend View';
            $data['templateDocs'] = 'https://docs.zendframework.com/zend-view/';
        }

        return new HtmlResponse($this->template->render('app::home-page', $data));
    }
}
