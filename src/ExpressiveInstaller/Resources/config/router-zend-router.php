<?php

declare(strict_types=1);

use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\ZendRouter;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => ZendRouter::class,
        ],
    ],
];
