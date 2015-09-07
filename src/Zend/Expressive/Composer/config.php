<?php

return [
    'packages' => [
        'aura/di'                           => '3.0.*@beta',
        'aura/router'                       => '^2.3',
        'league/plates'                     => '^3.1',
        'mouf/pimple-interop'               => '^1.0',
        'nikic/fast-route'                  => '^0.6.0',
        'ocramius/proxy-manager'            => '^1.0',
        'pimple/pimple'                     => '^3.0',
        'twig/twig'                         => '^1.21',
        'zendframework/zend-filter'         => '^2.5',
        'zendframework/zend-i18n'           => '^2.5',
        'zendframework/zend-mvc'            => '^2.5',
        'zendframework/zend-psr7bridge'     => '^0.1.0',
        'zendframework/zend-servicemanager' => '^2.5',
        'zendframework/zend-view'           => '^2.5',
    ],

    'questions' => [
        'router' => [
            'question'               => 'Which router you want to use?',
            'default'                => 1,
            // TRUE: Must choose one / FALSE: May choose one or none of the above
            'required'               => true,
            // Enable custom package input
            'custom-package'         => true,
            // Display warning when choosing a custom package
            'custom-package-warning' => 'You need to write your own router adapter.',
            'options'                => [
                1 => [
                    'name'     => 'Aura.Router',
                    'packages' => [
                        'aura/router',
                    ],
                    'copy-files' => [
                        // Copy source file to target: '<source>' => '<target>'
                        '/Resources/config/aura-router-routes.php' => '/config/autoload/router.global.php',
                    ],
                ],
                2 => [
                    'name'     => 'FastRoute',
                    'packages' => [
                        'nikic/fast-route',
                    ],
                    'copy-files' => [
                        '/Resources/config/fast-route-routes.php' => '/config/autoload/router.global.php',
                    ],
                ],
                3 => [
                    'name'     => 'zend-mvc TreeRouteStack',
                    'packages' => [
                        'zendframework/zend-mvc',
                        'zendframework/zend-psr7bridge',
                    ],
                    'copy-files' => [
                        '/Resources/config/zf2-router-routes.php' => '/config/autoload/router.global.php',
                    ],
                ],
            ],
        ],

        'container' => [
            'question'               => 'Which container you want to use for dependency injection?',
            'default'                => 4,
            'required'               => true,
            'custom-package'         => true,
            'custom-package-warning' => 'You need to edit public/index.php to start the custom container.',
            'options'                => [
                1 => [
                    'name'     => 'Aura.Di',
                    'packages' => [
                        'aura/di',
                    ],
                    'copy-files' => [
                        '/Resources/config/aura-di-container.php' => '/config/container.php',
                        '/Resources/config/container-dependencies.php' => '/config/autoload/dependencies.global.php',
                    ],
                ],
                2 => [
                    'name'     => 'Pimple <comment>Pimple 3.x / Needs testing!</comment>',
                    'packages' => [
                        'pimple/pimple',
                    ],
                    'copy-files' => [
                        '/Resources/config/pimple-container.php' => '/config/container.php',
                        '/Resources/config/container-dependencies.php' => '/config/autoload/dependencies.global.php',
                    ],
                ],
                3 => [
                    'name'     => 'Pimple-interop <comment>Pimple 1.x</comment>',
                    'packages' => [
                        'mouf/pimple-interop',
                    ],
                    'copy-files' => [
                        '/Resources/config/pimple-interop-container.php' => '/config/container.php',
                        '/Resources/config/container-dependencies.php' => '/config/autoload/dependencies.global.php',
                    ],
                ],
                4 => [
                    'name'     => 'zend-servicemanager',
                    'packages' => [
                        'zendframework/zend-servicemanager',
                        'ocramius/proxy-manager',
                    ],
                    'copy-files' => [
                        '/Resources/config/zend-servicemanager-container.php' => '/config/container.php',
                        '/Resources/config/container-dependencies.php' => '/config/autoload/dependencies.global.php',
                    ],
                ],
            ],
        ],

        'template-engine' => [
            'question'       => 'Which template engine you want to use?',
            'default'        => 'n',
            'required'       => false,
            'custom-package' => true,
            'options'        => [
                1 => [
                    'name'     => 'Plates',
                    'packages' => [
                        'league/plates',
                    ],
                    'copy-files' => [
                        '/Resources/config/plates-templates.php' => '/config/autoload/templates.global.php',
                        '/Resources/templates/plates-404.phtml' => '/templates/error/404.phtml',
                        '/Resources/templates/plates-500.phtml' => '/templates/error/500.phtml',
                        '/Resources/templates/plates-error.phtml' => '/templates/error/error.phtml',
                        '/Resources/templates/plates-layout.phtml' => '/templates/layout/default.phtml',
                        '/Resources/templates/plates-home-page.phtml' => '/templates/app/home-page.phtml',
                    ],
                ],
                2 => [
                    'name'     => 'Twig',
                    'packages' => [
                        'twig/twig',
                    ],
                    'copy-files' => [
                        '/Resources/config/twig-templates.php' => '/config/autoload/templates.global.php',
                        '/Resources/templates/twig-404.html.twig' => '/templates/error/404.html.twig',
                        '/Resources/templates/twig-500.html.twig' => '/templates/error/500.html.twig',
                        '/Resources/templates/twig-error.html.twig' => '/templates/error/error.html.twig',
                        '/Resources/templates/twig-layout.html.twig' => '/templates/layout/default.html.twig',
                        '/Resources/templates/twig-home-page.html.twig' => '/templates/app/home-page.html.twig',
                    ],
                ],
                3 => [
                    'name'     => 'zend-view <comment>installs zend-servicemanager</comment>',
                    'packages' => [
                        'zendframework/zend-view',
                        'zendframework/zend-filter',
                        'zendframework/zend-i18n',
                        'zendframework/zend-servicemanager',
                    ],
                    'copy-files' => [
                        '/Resources/config/zend-view-templates.php' => '/config/autoload/templates.global.php',
                        '/Resources/templates/zend-view-404.phtml' => '/templates/error/404.phtml',
                        '/Resources/templates/zend-view-500.phtml' => '/templates/error/500.phtml',
                        '/Resources/templates/zend-view-error.phtml' => '/templates/error/error.phtml',
                        '/Resources/templates/zend-view-layout.phtml' => '/templates/layout/default.phtml',
                        '/Resources/templates/zend-view-home-page.phtml' => '/templates/app/home-page.phtml',
                    ],
                ],
            ],
        ],
    ],
];
