<?php

/**
 * aura/router configuration
 */
return [
    'router' => [
        'routes' => [
            [
                'path' => '/',
                'middleware' => 'App\Action\HelloWorld',
                'allowed_methods' => ['GET']
            ],
            [
                'path' => '/ping',
                'middleware' => 'App\Action\Ping',
                'allowed_methods' => ['GET']
            ]
        ]
    ],

    'service_manager' => [
        'factories' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\Aura::class
        ]
    ]
];
