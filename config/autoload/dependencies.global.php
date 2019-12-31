<?php

return [
    'dependencies' => [
        'invokables' => [
            App\Action\PingAction::class => App\Action\PingAction::class,
        ],
        'factories' => [
            App\Action\HomePageAction::class => App\Action\HomePageFactory::class,
            Mezzio\Application::class => Mezzio\Container\ApplicationFactory::class,
        ]
    ]
];
