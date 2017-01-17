<?php

use App\Action;
use Zend\Expressive\Router\AuraRouter;
use Zend\Expressive\Router\RouterInterface;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => AuraRouter::class,
            Action\PingAction::class => Action\PingAction::class,
        ],
        'factories' => [
            Action\HomePageAction::class => Action\HomePageFactory::class,
        ],
    ],
];
