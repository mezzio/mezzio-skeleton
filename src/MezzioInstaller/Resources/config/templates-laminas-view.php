<?php

return [
    'dependencies' => [
        'factories' => [
            'Mezzio\FinalHandler' =>
                Mezzio\Container\TemplatedErrorHandlerFactory::class,

            Mezzio\Template\TemplateRendererInterface::class =>
                Mezzio\LaminasView\LaminasViewRendererFactory::class,

            Laminas\View\HelperPluginManager::class =>
                Mezzio\LaminasView\HelperPluginManagerFactory::class,
        ],
    ],

    'templates' => [
        'layout' => 'layout/default',
        'map' => [
            'layout/default' => 'templates/layout/default.phtml',
            'error/error'    => 'templates/error/error.phtml',
            'error/404'      => 'templates/error/404.phtml',
        ],
        'paths' => [
            'app'    => ['templates/app'],
            'layout' => ['templates/layout'],
            'error'  => ['templates/error'],
        ],
    ],

    'view_helpers' => [
        // laminas-servicemanager-style configuration for adding view helpers:
        // - 'aliases'
        // - 'invokables'
        // - 'factories'
        // - 'abstract_factories'
        // - etc.
    ],
];
