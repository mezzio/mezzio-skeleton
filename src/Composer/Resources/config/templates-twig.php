<?php

return [
    'dependencies' => [
        'factories' => [
            'Mezzio\FinalHandler' =>
                Mezzio\Container\TemplatedErrorHandlerFactory::class,

            Mezzio\Template\TemplateRendererInterface::class =>
                Mezzio\Twig\TwigRendererFactory::class,
        ],
    ],

    'templates' => [
        'cache_dir' => 'data/cache/twig',
        'assets_url' => '/',
        'assets_version' => null,
        'extension' => 'html.twig',
        'paths' => [
            'app'    => ['templates/app'],
            'layout' => ['templates/layout'],
            'error'  => ['templates/error'],
        ]
    ]
];
