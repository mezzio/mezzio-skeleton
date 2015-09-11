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
            'app'    => 'templates/app',
            'layout' => 'templates/layout',
            'error'  => 'templates/error',
        ]
    ]
];
