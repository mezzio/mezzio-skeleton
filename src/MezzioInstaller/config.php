<?php

declare(strict_types=1);

// @phpcs:disable Generic.Files.LineLength.TooLong
return [
    'packages'    => [
        'chubbyphp/chubbyphp-laminas-config' => [
            'version' => '^1.3',
        ],
        'elie29/zend-phpdi-config'           => [
            'version' => '^9.0',
        ],
        'filp/whoops'                        => [
            'version' => '^2.15.4',
        ],
        'jsoumelidis/zend-sf-di-config'      => [
            'version' => '^0.5',
        ],
        'mezzio/mezzio-fastroute'            => [
            'version'   => '^3.11.0',
            'whitelist' => [
                'mezzio/mezzio-fastroute',
            ],
        ],
        'mezzio/mezzio-platesrenderer'       => [
            'version'   => '^2.10',
            'whitelist' => [
                'mezzio/mezzio-platesrenderer',
            ],
        ],
        'mezzio/mezzio-twigrenderer'         => [
            'version'   => '^2.15',
            'whitelist' => [
                'mezzio/mezzio-twigrenderer',
            ],
        ],
        'mezzio/mezzio-laminasrouter'        => [
            'version'   => '^3.9.0',
            'whitelist' => [
                'mezzio/mezzio-laminasrouter',
            ],
        ],
        'mezzio/mezzio-laminasviewrenderer'  => [
            'version'   => '^2.15',
            'whitelist' => [
                'mezzio/mezzio-laminasviewrenderer',
            ],
        ],
        'laminas/laminas-servicemanager'     => [
            'version' => '^3.22',
        ],
    ],
    'require-dev' => [
        'filp/whoops',
    ],
    'application' => [
        'flat'    => [
            'packages'  => [],
            'resources' => [
                'Resources/src/ConfigProvider.flat.php' => 'src/App/ConfigProvider.php',
            ],
        ],
        'modular' => [
            'packages'  => [],
            'resources' => [
                'Resources/src/ConfigProvider.modular.php' => 'src/App/src/ConfigProvider.php',
            ],
        ],
    ],
    'questions'   => [
        'container'       => [
            'question'               => 'Which container do you want to use for dependency injection?',
            'default'                => 1,
            'required'               => true,
            'custom-package'         => true,
            'custom-package-warning' => 'You need to edit public/index.php to start the custom container.',
            'options'                => [
                1 => [
                    'name'     => 'laminas-servicemanager <comment>(supported by laminas)</comment>',
                    'packages' => [
                        'laminas/laminas-servicemanager',
                    ],
                    'flat'     => [
                        'Resources/config/container-laminas-servicemanager.php' => 'config/container.php',
                    ],
                    'modular'  => [
                        'Resources/config/container-laminas-servicemanager.php' => 'config/container.php',
                    ],
                    'minimal'  => [
                        'Resources/config/container-laminas-servicemanager.php' => 'config/container.php',
                    ],
                ],
                2 => [
                    'name'     => 'Symfony DI Container',
                    'packages' => [
                        'jsoumelidis/zend-sf-di-config',
                    ],
                    'flat'     => [
                        'Resources/config/container-sf-di.php' => 'config/container.php',
                    ],
                    'modular'  => [
                        'Resources/config/container-sf-di.php' => 'config/container.php',
                    ],
                    'minimal'  => [
                        'Resources/config/container-sf-di.php' => 'config/container.php',
                    ],
                ],
                3 => [
                    'name'     => 'PHP-DI',
                    'packages' => [
                        'elie29/zend-phpdi-config',
                    ],
                    'flat'     => [
                        'Resources/config/container-php-di.php' => 'config/container.php',
                    ],
                    'modular'  => [
                        'Resources/config/container-php-di.php' => 'config/container.php',
                    ],
                    'minimal'  => [
                        'Resources/config/container-php-di.php' => 'config/container.php',
                    ],
                ],
                4 => [
                    'name'     => 'chubbyphp-container',
                    'packages' => [
                        'chubbyphp/chubbyphp-laminas-config',
                    ],
                    'flat'     => [
                        'Resources/config/container-chubbyphp.php' => 'config/container.php',
                    ],
                    'modular'  => [
                        'Resources/config/container-chubbyphp.php' => 'config/container.php',
                    ],
                    'minimal'  => [
                        'Resources/config/container-chubbyphp.php' => 'config/container.php',
                    ],
                ],
            ],
        ],
        'router'          => [
            'question' => 'Which router do you want to use?',
            'default'  => 1,
            // TRUE: Must choose one / FALSE: May choose one or none of the above
            'required' => true,
            // Enable custom package input
            'custom-package' => true,
            // Display warning when choosing a custom package
            'custom-package-warning' => 'You need to write your own router adapter.',
            'options'                => [
                1 => [
                    'name'     => 'FastRoute <comment>(supported by laminas)</comment>',
                    'packages' => [
                        'mezzio/mezzio-fastroute',
                    ],
                    'flat'     => [
                        'Resources/config/routes-fastroute-full.php' => 'config/routes.php',
                    ],
                    'modular'  => [
                        'Resources/config/routes-fastroute-full.php' => 'config/routes.php',
                    ],
                    'minimal'  => [
                        'Resources/config/routes-fastroute-minimal.php' => 'config/routes.php',
                    ],
                ],
                2 => [
                    'name'     => 'laminas-router <comment>(supported by laminas)</comment>',
                    'packages' => [
                        'mezzio/mezzio-laminasrouter',
                    ],
                    'flat'     => [
                        'Resources/config/routes-laminas-router-full.php' => 'config/routes.php',
                    ],
                    'modular'  => [
                        'Resources/config/routes-laminas-router-full.php' => 'config/routes.php',
                    ],
                    'minimal'  => [
                        'Resources/config/routes-laminas-router-minimal.php' => 'config/routes.php',
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
                    'name'     => 'Plates <comment>(supported by laminas)</comment>',
                    'packages' => [
                        'mezzio/mezzio-platesrenderer',
                    ],
                    'flat'     => [
                        'Resources/templates/plates/404.phtml'       => 'templates/error/404.phtml',
                        'Resources/templates/plates/error.phtml'     => 'templates/error/error.phtml',
                        'Resources/templates/plates/layout.phtml'    => 'templates/layout/default.phtml',
                        'Resources/templates/plates/home-page.phtml' => 'templates/app/home-page.phtml',
                    ],
                    'modular'  => [
                        'Resources/templates/plates/404.phtml'       => 'src/App/templates/error/404.phtml',
                        'Resources/templates/plates/error.phtml'     => 'src/App/templates/error/error.phtml',
                        'Resources/templates/plates/layout.phtml'    => 'src/App/templates/layout/default.phtml',
                        'Resources/templates/plates/home-page.phtml' => 'src/App/templates/app/home-page.phtml',
                    ],
                    'minimal'  => [],
                ],
                2 => [
                    'name'     => 'Twig <comment>(supported by laminas)</comment>',
                    'packages' => [
                        'mezzio/mezzio-twigrenderer',
                    ],
                    'flat'     => [
                        'Resources/templates/twig/404.html.twig'       => 'templates/error/404.html.twig',
                        'Resources/templates/twig/error.html.twig'     => 'templates/error/error.html.twig',
                        'Resources/templates/twig/layout.html.twig'    => 'templates/layout/default.html.twig',
                        'Resources/templates/twig/home-page.html.twig' => 'templates/app/home-page.html.twig',
                    ],
                    'modular'  => [
                        'Resources/templates/twig/404.html.twig'       => 'src/App/templates/error/404.html.twig',
                        'Resources/templates/twig/error.html.twig'     => 'src/App/templates/error/error.html.twig',
                        'Resources/templates/twig/layout.html.twig'    => 'src/App/templates/layout/default.html.twig',
                        'Resources/templates/twig/home-page.html.twig' => 'src/App/templates/app/home-page.html.twig',
                    ],
                    'minimal'  => [],
                ],
                3 => [
                    'name'     => 'laminas-view <comment>installs laminas-servicemanager (supported by laminas)</comment>',
                    'packages' => [
                        'mezzio/mezzio-laminasviewrenderer',
                    ],
                    'flat'     => [
                        'Resources/templates/laminas-view/404.phtml'       => 'templates/error/404.phtml',
                        'Resources/templates/laminas-view/error.phtml'     => 'templates/error/error.phtml',
                        'Resources/templates/laminas-view/layout.phtml'    => 'templates/layout/default.phtml',
                        'Resources/templates/laminas-view/home-page.phtml' => 'templates/app/home-page.phtml',
                    ],
                    'modular'  => [
                        'Resources/templates/laminas-view/404.phtml'       => 'src/App/templates/error/404.phtml',
                        'Resources/templates/laminas-view/error.phtml'     => 'src/App/templates/error/error.phtml',
                        'Resources/templates/laminas-view/layout.phtml'    => 'src/App/templates/layout/default.phtml',
                        'Resources/templates/laminas-view/home-page.phtml' => 'src/App/templates/app/home-page.phtml',
                    ],
                    'minimal'  => [],
                ],
            ],
        ],
        'error-handler'   => [
            'question'       => 'Which error handler do you want to use during development?',
            'default'        => 1,
            'required'       => false,
            'custom-package' => true,
            'force'          => true,
            'options'        => [
                1 => [
                    'name'     => 'Whoops <comment>(supported by laminas)</comment>',
                    'packages' => [
                        'filp/whoops',
                    ],
                    'flat'     => [
                        'Resources/config/error-handler-whoops.php' => 'config/autoload/global.dev.php.dist',
                    ],
                    'modular'  => [
                        'Resources/config/error-handler-whoops.php' => 'config/autoload/global.dev.php.dist',
                    ],
                    'minimal'  => [
                        'Resources/config/error-handler-whoops.php' => 'config/autoload/global.dev.php.dist',
                    ],
                ],
            ],
        ],
    ],
];
