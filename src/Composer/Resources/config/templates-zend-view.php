<?php

return [
    'dependencies' => [
        'factories' => [
            'Zend\Expressive\FinalHandler' =>
                Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,

            Zend\Expressive\Template\TemplateInterface::class =>
                Zend\Expressive\Container\Template\ZendViewFactory::class,
        ],
    ],

    'templates' => [
        //'layout' => 'name of layout view to use, if any',
        'map' => [
            'layout/default' => 'templates/layout/default.phtml',
            'error/error'    => 'templates/error/error.phtml',
            'error/404'      => 'templates/error/404.phtml',
        ],
        'paths' => [
            'templates/app'    => 'app',
            'templates/layout' => 'layout',
            'templates/error'  => 'error',
        ]
    ]
];
