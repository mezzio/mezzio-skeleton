<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
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

        return new HtmlResponse($this->getRenderer()->render('app::home-page', [
            'entry' => 'first entry'
        ]));
    }
}
