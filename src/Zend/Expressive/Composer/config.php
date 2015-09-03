<?php

return [
    'packages' => [
        'aura/router'                       => '^2.3',
        'league/plates'                     => '^3.1',
        'mouf/pimple-interop'               => '^1.0',
        'nikic/fast-route'                  => '^0.6.0',
        'twig/twig'                         => '^1.19',
        'zendframework/zend-mvc'            => '^2.5',
        'zendframework/zend-psr7bridge'     => '^0.1.0',
        'zendframework/zend-servicemanager' => '^2.5',
        'zendframework/zend-view'           => '^2.5',
    ],

    'questions' => [
        'router' => [
            'question'               => 'Which router you want to use?',
            'required'               => true, // TRUE: Must choose one / FALSE: May choose one or none of the above
            'custom-package'         => true, // Enable custom package input
            'custom-package-warning' => 'You need to write your own router adapter.', // Display warning when choosing a custom package
            'options'                => [
                1 => [
                    'name'     => 'aura/router',
                    'packages' => [
                        'aura/router',
                    ],
                    'copy-files' => [
                        '/Resources/config/aura-router.php' => '/config/autoload/router.global.php', // Copy source file to target
                    ],
                ],
                2 => [
                    'name'     => 'nikic/fast-route',
                    'packages' => [
                        'nikic/fast-route',
                    ],
                ],
                3 => [
                    'name'     => 'zend-mvc TreeRouteStack',
                    'packages' => [
                        'zendframework/zend-mvc',
                        'zendframework/zend-psr7bridge',
                    ],
                ],
            ],
        ],

        'container' => [
            'question'               => 'Which container you want to use for dependency injection?',
            'required'               => true,
            'custom-package'         => true,
            'custom-package-warning' => 'You need to edit public/index.php to start the custom container.',
            'options'                => [
                1 => [
                    'name'     => 'zendframework/zend-servicemanager',
                    'packages' => [
                        'zendframework/zend-servicemanager',
                    ],
                    'copy-files' => [
                        '/Resources/public/zend-index.php' => '/public/index.php',
                    ],
                ],
                2 => [
                    'name'     => 'mouf/pimple-interop',
                    'packages' => [
                        'mouf/pimple-interop',
                    ],
                    'copy-files' => [
                        '/Resources/public/pimple-index.php' => '/public/index.php',
                    ],
                ],
            ],
        ],

        'template-engine' => [
            'question'       => 'Which template engine you want to use?',
            'required'       => false,
            'custom-package' => true,
            'options'        => [
                1 => [
                    'name'     => 'zendframework/zend-view',
                    'packages' => [
                        'zendframework/zend-view',
                    ],
                ],
                2 => [
                    'name'     => 'league/plates',
                    'packages' => [
                        'league/plates',
                    ],
                ],
                3 => [
                    'name'     => 'twig/twig',
                    'packages' => [
                        'twig/twig',
                    ],
                ],
            ],
        ],
    ],
];
