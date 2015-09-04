<?php

return [
    'dependencies' => [
        'factories' => [
            'Zend\Expressive\FinalHandler' => Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,
            Zend\Expressive\Template\TemplateInterface::class => App\Template\TwigFactory::class,
        ],
    ],

    'templates' => [
        'cache_dir' => 'data/cache/twig',
        'extension' => 'html.twig',
        'assets_url' => '/', // Path prefix or CDN url
        'assets_version' => null, // Version to place behind assets
        'paths' => [
            'templates/app' => 'app',
            'templates/layout' => 'layout'
        ]
    ]
];
