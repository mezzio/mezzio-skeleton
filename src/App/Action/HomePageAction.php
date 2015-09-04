<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateInterface;

class HomePageAction extends AbstractAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        if (!$this->getContainer()->has(TemplateInterface::class)) {
            return new JsonResponse([
                'welcome' => 'Congratulations! You have successfully installed the zend-expressive Skeleton Application',
                'docs' => 'https://zend-expressive.readthedocs.org/en/latest/'
            ]);
        }

        $data = [];

        $router = $this->getContainer()->get(RouterInterface::class);
        if ($router instanceof Zend\Expressive\Router\AuraRouter) {
            $data['routerDocsUrl'] = 'https://zend-expressive.readthedocs.org/en/latest/router/aura/';
            $data['routerName'] = 'Aura.Router';
            $data['routerExtUrl'] = 'http://auraphp.com/packages/Aura.Router/';
        } elseif ($router instanceof Zend\Expressive\Router\FastRouteRouter) {
            $data['routerDocsUrl'] = 'https://zend-expressive.readthedocs.org/en/latest/router/fast-route/';
            $data['routerName'] = 'FastRoute';
            $data['routerExtUrl'] = 'https://github.com/nikic/FastRoute';
        } elseif ($router instanceof Zend\Expressive\Router\Zf2Router) {
            $data['routerDocsUrl'] = 'https://zend-expressive.readthedocs.org/en/latest/router/zf2/';
            $data['routerName'] = 'ZF2 Router';
            $data['routerExtUrl'] = 'http://framework.zend.com/manual/current/en/modules/zend.mvc.routing.html';
        }

        return new HtmlResponse($this->getRenderer()->render('app::home-page', [
            $data
        ]));
    }
}
