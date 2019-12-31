<?php

use Mezzio\Router\LaminasRouter;
use Mezzio\Router\RouterInterface;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => LaminasRouter::class,
        ],
    ],
];
