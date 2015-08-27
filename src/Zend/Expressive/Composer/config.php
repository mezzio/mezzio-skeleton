<?php

return [
    'packages' => [
        'aura/router' => '^2.3',
        'league/plates' => '^3.1',
        'mouf/pimple-interop' => '^1.0',
        'nikic/fast-route' => '^0.6.0',
        'twig/twig' => '^1.19',
        'zendframework/zend-mvc' => '^2.5',
        'zendframework/zend-psr7bridge' => '^0.1.0',
        'zendframework/zend-servicemanager' => '^2.5',
        'zendframework/zend-view' => '^2.5',
    ],

    'questions' => [

        'routers' => [
            'question' => 'Which router you want to use?',
            'options' => [
                1 => [
                    'name' => 'aura/router',
                    'packages' => [
                        'aura/router'
                    ]
                ],
                2 => [
                    'name' => 'nikic/fast-route',
                    'packages' => [
                        'nikic/fast-route'
                    ]
                ],
                3 => [
                    'name' => 'zend-mvc TreeRouteStack',
                    'packages' => [
                        'zendframework/zend-mvc',
                        'zendframework/zend-psr7bridge'
                    ]
                ]
            ]
        ],

        'containers' => [
            'question' => 'Which container you want to use for dependency injection?',
            'options' => [
                1 => [
                    'name' => 'zendframework/zend-servicemanager',
                    'packages' => [
                        'zendframework/zend-servicemanager'
                    ]
                ],
                2 => [
                    'name' => 'mouf/pimple-interop',
                    'packages' => [
                        'mouf/pimple-interop'
                    ]
                ]
            ]
        ],

        'template-adapters' => [
            'question' => 'Which template adapter you want to use?',
            'options' => [
                1 => [
                    'name' => 'zendframework/zend-view',
                    'packages' => [
                        'zendframework/zend-view'
                    ]
                ],
                2 => [
                    'name' => 'league/plates',
                    'packages' => [
                        'league/plates'
                    ]
                ],
                3 => [
                    'name' => 'twig/twig',
                    'packages' => [
                        'twig/twig'
                    ]
                ]
            ]
        ]
    ]
];
