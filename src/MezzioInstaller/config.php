<?php

declare(strict_types=1);

return [
    'packages' => [
        'filp/whoops'                                    => '^2.1.12',
        'jsoumelidis/zend-sf-di-config'                  => '^0.1',
        'northwoods/container'                           => '^1.2',
        'laminas/laminas-auradi-config'               => '^1.0.0-dev',
        'mezzio/mezzio-aurarouter'       => '^3.0.0alpha2 || ^3.0',
        'mezzio/mezzio-fastroute'        => '^3.0.0alpha1 || ^3.0',
        'mezzio/mezzio-platesrenderer'   => '^2.0.0alpha1 || ^2.0',
        'mezzio/mezzio-twigrenderer'     => '^2.0.0alpha1 || ^2.0',
        'mezzio/mezzio-laminasrouter'       => '^3.0.0alpha1 || ^3.0',
        'mezzio/mezzio-laminasviewrenderer' => '^2.0.0alpha1 || ^2.0',
        'laminas/laminas-pimple-config'               => '^1.0.0-dev',
        'laminas/laminas-servicemanager'              => '^3.3',
    ],

    'require-dev' => [
        'filp/whoops',
        'mezzio/mezzio-tooling',
    ],

    'application' => [
        'flat' => [
            'packages' => [],
            'resources' => [
                'Resources/src/ConfigProvider.flat.php' => 'src/App/ConfigProvider.php',
            ],
        ],
        'modular' => [
            'packages' => [
                'mezzio/mezzio-tooling' => '^1.0.0alpha1 || ^1.0',
            ],
            'resources' => [
                'Resources/src/ConfigProvider.modular.php' => 'src/App/src/ConfigProvider.php',
            ],
        ],
    ],

    'questions' => [
        'container' => [
            'question'               => 'Which container do you want to use for dependency injection?',
            'default'                => 3,
            'required'               => true,
            'custom-package'         => true,
            'custom-package-warning' => 'You need to edit public/index.php to start the custom container.',
            'options'                => [
                1 => [
                    'name'     => 'Aura.Di',
                    'packages' => [
                        'laminas/laminas-auradi-config',
                    ],
                    'flat' => [
                        'Resources/config/container-aura-di.php' => 'config/container.php',
                    ],
                    'modular' => [
                        'Resources/config/container-aura-di.php' => 'config/container.php',
                    ],
                    'minimal' => [
                        'Resources/config/container-aura-di.php' => 'config/container.php',
                    ],
                ],
                2 => [
                    'name'     => 'Pimple',
                    'packages' => [
                        'laminas/laminas-pimple-config',
                    ],
                    'flat' => [
                        'Resources/config/container-pimple.php' => 'config/container.php',
                    ],
                    'modular' => [
                        'Resources/config/container-pimple.php' => 'config/container.php',
                    ],
                    'minimal' => [
                        'Resources/config/container-pimple.php' => 'config/container.php',
                    ],
                ],
                3 => [
                    'name'     => 'Laminas ServiceManager',
                    'packages' => [
                        'laminas/laminas-servicemanager',
                    ],
                    'flat' => [
                        'Resources/config/container-laminas-servicemanager.php' => 'config/container.php',
                    ],
                    'modular' => [
                        'Resources/config/container-laminas-servicemanager.php' => 'config/container.php',
                    ],
                    'minimal' => [
                        'Resources/config/container-laminas-servicemanager.php' => 'config/container.php',
                    ],
                ],
                4 => [
                    'name'     => 'Auryn',
                    'packages' => [
                        'northwoods/container',
                    ],
                    'flat' => [
                        'Resources/config/container-auryn.php' => 'config/container.php',
                    ],
                    'modular' => [
                        'Resources/config/container-auryn.php' => 'config/container.php',
                    ],
                    'minimal' => [
                        'Resources/config/container-auryn.php' => 'config/container.php',
                    ],
                ],
                5 => [
                    'name'     => 'Symfony DI Container',
                    'packages' => [
                        'jsoumelidis/zend-sf-di-config',
                    ],
                    'flat' => [
                        'Resources/config/container-sf-di.php' => 'config/container.php',
                    ],
                    'modular' => [
                        'Resources/config/container-sf-di.php' => 'config/container.php',
                    ],
                    'minimal' => [
                        'Resources/config/container-sf-di.php' => 'config/container.php',
                    ],
                ],
            ],
        ],

        'router' => [
            'question'               => 'Which router do you want to use?',
            'default'                => 2,
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
                        'mezzio/mezzio-aurarouter',
                    ],
                    'flat' => [
                        'Resources/config/routes-full.php' => 'config/routes.php',
                    ],
                    'modular' => [
                        'Resources/config/routes-full.php' => 'config/routes.php',
                    ],
                    'minimal' => [
                        'Resources/config/routes-minimal.php' => 'config/routes.php',
                    ],
                ],
                2 => [
                    'name'     => 'FastRoute',
                    'packages' => [
                        'mezzio/mezzio-fastroute',
                    ],
                    'flat' => [
                        'Resources/config/routes-full.php' => 'config/routes.php',
                    ],
                    'modular' => [
                        'Resources/config/routes-full.php' => 'config/routes.php',
                    ],
                    'minimal' => [
                        'Resources/config/routes-minimal.php' => 'config/routes.php',
                    ],
                ],
                3 => [
                    'name'     => 'Laminas Router',
                    'packages' => [
                        'mezzio/mezzio-laminasrouter',
                    ],
                    'flat' => [
                        'Resources/config/routes-full.php' => 'config/routes.php',
                    ],
                    'modular' => [
                        'Resources/config/routes-full.php' => 'config/routes.php',
                    ],
                    'minimal' => [
                        'Resources/config/routes-minimal.php' => 'config/routes.php',
                    ],
                ],
            ],
        ],

        'template-engine' => [
            'question'       => 'Which template engine do you want to use?',
            'default'        => 'n',
            'required'       => false,
            'custom-package' => true,
            'options'        => [
                1 => [
                    'name'     => 'Plates',
                    'packages' => [
                        'mezzio/mezzio-platesrenderer',
                    ],
                    'flat' => [
                        'Resources/templates/plates-404.phtml'       => 'templates/error/404.phtml',
                        'Resources/templates/plates-error.phtml'     => 'templates/error/error.phtml',
                        'Resources/templates/plates-layout.phtml'    => 'templates/layout/default.phtml',
                        'Resources/templates/plates-home-page.phtml' => 'templates/app/home-page.phtml',
                    ],
                    'modular' => [
                        'Resources/templates/plates-404.phtml'       => 'src/App/templates/error/404.phtml',
                        'Resources/templates/plates-error.phtml'     => 'src/App/templates/error/error.phtml',
                        'Resources/templates/plates-layout.phtml'    => 'src/App/templates/layout/default.phtml',
                        'Resources/templates/plates-home-page.phtml' => 'src/App/templates/app/home-page.phtml',
                    ],
                    'minimal' => [
                    ],
                ],
                2 => [
                    'name'     => 'Twig',
                    'packages' => [
                        'mezzio/mezzio-twigrenderer',
                    ],
                    'flat' => [
                        'Resources/templates/twig-404.html.twig'       => 'templates/error/404.html.twig',
                        'Resources/templates/twig-error.html.twig'     => 'templates/error/error.html.twig',
                        'Resources/templates/twig-layout.html.twig'    => 'templates/layout/default.html.twig',
                        'Resources/templates/twig-home-page.html.twig' => 'templates/app/home-page.html.twig',
                    ],
                    'modular' => [
                        'Resources/templates/twig-404.html.twig'       => 'src/App/templates/error/404.html.twig',
                        'Resources/templates/twig-error.html.twig'     => 'src/App/templates/error/error.html.twig',
                        'Resources/templates/twig-layout.html.twig'    => 'src/App/templates/layout/default.html.twig',
                        'Resources/templates/twig-home-page.html.twig' => 'src/App/templates/app/home-page.html.twig',
                    ],
                    'minimal' => [
                    ],
                ],
                3 => [
                    'name'     => 'Laminas View <comment>installs Laminas ServiceManager</comment>',
                    'packages' => [
                        'mezzio/mezzio-laminasviewrenderer',
                    ],
                    'flat' => [
                        'Resources/templates/laminas-view-404.phtml'       => 'templates/error/404.phtml',
                        'Resources/templates/laminas-view-error.phtml'     => 'templates/error/error.phtml',
                        'Resources/templates/laminas-view-layout.phtml'    => 'templates/layout/default.phtml',
                        'Resources/templates/laminas-view-home-page.phtml' => 'templates/app/home-page.phtml',
                    ],
                    'modular' => [
                        'Resources/templates/laminas-view-404.phtml'       => 'src/App/templates/error/404.phtml',
                        'Resources/templates/laminas-view-error.phtml'     => 'src/App/templates/error/error.phtml',
                        'Resources/templates/laminas-view-layout.phtml'    => 'src/App/templates/layout/default.phtml',
                        'Resources/templates/laminas-view-home-page.phtml' => 'src/App/templates/app/home-page.phtml',
                    ],
                    'minimal' => [
                    ],
                ],
            ],
        ],

        'error-handler' => [
            'question'       => 'Which error handler do you want to use during development?',
            'default'        => 1,
            'required'       => false,
            'custom-package' => true,
            'force'          => true,
            'options'        => [
                1 => [
                    'name'     => 'Whoops',
                    'packages' => [
                        'filp/whoops',
                    ],
                    'flat' => [
                        'Resources/config/error-handler-whoops.php' => 'config/autoload/development.local.php.dist',
                    ],
                    'modular' => [
                        'Resources/config/error-handler-whoops.php' => 'config/autoload/development.local.php.dist',
                    ],
                    'minimal' => [
                        'Resources/config/error-handler-whoops.php' => 'config/autoload/development.local.php.dist',
                    ],
                ],
            ],
        ],
    ],
];
