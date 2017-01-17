<?php

use Zend\Expressive\Router\AuraRouter;
use Zend\Expressive\Router\RouterInterface;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => AuraRouter::class,
        ],
        // Map middleware -> factories here
        'factories' => [
        ],
    ],
];
