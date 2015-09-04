<?php

return [
    'dependencies' => [
        'factories' => [
            Zend\Expressive\Application::class => Zend\Expressive\Container\ApplicationFactory::class,
            'Zend\Expressive\FinalHandler' => Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,
            Zend\Expressive\Template\TemplateInterface::class => App\Template\TwigFactory::class,

            App\Action\HomePageAction::class => App\Action\HomePageFactory::class,
            App\Action\PingAction::class => App\Action\PingFactory::class,
        ]
    ]
];
