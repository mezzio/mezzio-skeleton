<?php

namespace App\Action;

use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PingAction extends AbstractAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        //$response->getBody()->write('home');
        //return $response;

        return new JsonResponse(['ack' => time()]);
    }
}
