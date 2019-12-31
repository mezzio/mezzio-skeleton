<?php

use Mezzio\Router\AuraRouter;
use Mezzio\Router\RouterInterface;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => AuraRouter::class,
        ],
    ],
];
