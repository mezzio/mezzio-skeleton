<?php

return [
    'routes' => [
        [
            'path' => '/',
            'middleware' => App\Action\HomePageAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'path' => '/ping',
            'middleware' => App\Action\PingAction::class,
            'allowed_methods' => ['GET']
        ],
    ]
];
