<?php

use App\Action;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\ZendRouter;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => ZendRouter::class,
            Action\PingAction::class => Action\PingAction::class,
        ],
        'factories' => [
            Action\HomePageAction::class => Action\HomePageFactory::class,
        ],
    ],
];
