<?php

return [
    'dependencies' => [
        'factories' => [
            'Zend\Expressive\FinalHandler' => Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,
            Zend\Expressive\Template\TemplateInterface::class => App\Template\ZendViewFactory::class,
        ],
    ],

    'templates' => [
        'cache_dir'       => 'data/cache/zend-view',
        'extension'       => 'php',
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
