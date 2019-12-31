<?php

use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouterInterface;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => FastRouteRouter::class,
        ],
    ],
];
