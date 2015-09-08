<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template\TemplateInterface;

class HomePageAction
{
    private $router;

    private $template;

    public function __construct(Router\RouterInterface $router, TemplateInterface $template = null)
    {
        $this->router   = $router;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        if (!$this->template) {
            return new JsonResponse([
                'welcome' => 'Congratulations! You have installed the zend-expressive skeleton application',
                'docs'    => 'https://zend-expressive.readthedocs.org/en/latest/'
            ]);
        }

        $data = [];

        if ($this->router instanceof Router\AuraRouter) {
            $data['routerDocsUrl'] = 'https://zend-expressive.readthedocs.org/en/latest/router/aura/';
            $data['routerName']    = 'Aura.Router';
            $data['routerExtUrl']  = 'http://auraphp.com/packages/Aura.Router/';
        } elseif ($this->router instanceof Router\FastRouteRouter) {
            $data['routerDocsUrl'] = 'https://zend-expressive.readthedocs.org/en/latest/router/fast-route/';
            $data['routerName']    = 'FastRoute';
            $data['routerExtUrl']  = 'https://github.com/nikic/FastRoute';
        } elseif ($this->router instanceof Router\Zf2Router) {
            $data['routerDocsUrl'] = 'https://zend-expressive.readthedocs.org/en/latest/router/zf2/';
            $data['routerName']    = 'ZF2 Router';
            $data['routerExtUrl']  = 'http://framework.zend.com/manual/current/en/modules/zend.mvc.routing.html';
        }

        return new HtmlResponse($this->template->render('app::home-page', $data));
    }
}
