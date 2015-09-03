<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class HomePageAction extends AbstractAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return new HtmlResponse($this->getRenderer()->render('app::home-page', [
            'entry' => 'first entry'
        ]));
    }
}
