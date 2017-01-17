<?php

use App\Action;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\RouterInterface;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => FastRouteRouter::class,
            Action\PingAction::class => Action\PingAction::class,
        ],
        'factories' => [
            Action\HomePageAction::class => Action\HomePageFactory::class,
        ],
    ],
];
