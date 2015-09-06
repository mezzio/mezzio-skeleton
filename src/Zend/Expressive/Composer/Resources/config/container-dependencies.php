<?php

return [
    'dependencies' => [
        'factories' => [
            Zend\Expressive\Application::class => Zend\Expressive\Container\ApplicationFactory::class,

            App\Action\HomePageAction::class => App\Action\HomePageFactory::class,
            App\Action\PingAction::class => App\Action\PingFactory::class,
        ]
    ]
];
