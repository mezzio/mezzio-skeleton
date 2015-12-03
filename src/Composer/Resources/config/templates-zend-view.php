<?php

return [
    'dependencies' => [
        /*
         * Note: delegator_factories only work with zend-servicemanager.
         *
         * To get equivalent functionality with Pimple, add the following to your
         * config/container.php file:
         *
         * $container->extend(Zend\Expressive\Application::class, function ($app, $container) {
         *     $app->attachRouteResultObserver($container->get(Zend\Expressive\ZendView\UrlHelper::class));
         *     return $app;
         * });
         */
        'delegator_factories' => [
            Zend\Expressive\Application::class => [
                Zend\Expressive\ZendView\ApplicationUrlDelegatorFactory::class,
            ],
        ],
        'factories' => [
            'Zend\Expressive\FinalHandler' =>
                Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,

            Zend\Expressive\ZendView\UrlHelper::class => Zend\Expressive\ZendView\UrlHelperFactory::class,

            Zend\Expressive\Template\TemplateRendererInterface::class =>
                Zend\Expressive\ZendView\ZendViewRendererFactory::class,
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
        ]
    ]
];
