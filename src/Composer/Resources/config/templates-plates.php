<?php

return [
    'dependencies' => [
        'factories' => [
            'Zend\Expressive\FinalHandler' =>
                Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,

            Zend\Expressive\Template\TemplateInterface::class =>
                Zend\Expressive\Container\Template\PlatesFactory::class,
        ],
    ],

    'templates' => [
        'extension' => 'phtml',
        'paths' => [
            'templates/app' => 'app',
            'templates/layout' => 'layout',
            'templates/error' => 'error',
        ]
    ]
];
