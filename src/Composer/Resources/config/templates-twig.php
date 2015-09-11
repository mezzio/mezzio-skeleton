<?php

return [
    'dependencies' => [
        'factories' => [
            'Zend\Expressive\FinalHandler' =>
                Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,

            Zend\Expressive\Template\TemplateInterface::class =>
                Zend\Expressive\Container\Template\TwigFactory::class,
        ],
    ],

    'templates' => [
        'cache_dir' => 'data/cache/twig',
        'assets_url' => '/',
        'assets_version' => null,
        'extension' => 'html.twig',
        'paths' => [
            'templates/app' => 'app',
            'templates/layout' => 'layout',
            'templates/error' => 'error',
        ]
    ]
];
