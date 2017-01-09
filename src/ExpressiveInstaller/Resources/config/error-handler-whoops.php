<?php

use Zend\Expressive\Container;
use Zend\Expressive\Middleware\ErrorResponseGenerator;

return [
    'dependencies' => [
        'invokables' => [
        ],
        'factories'  => [
            ErrorResponseGenerator::class       => Container\WhoopsErrorResponseGeneratorFactory::class,
            'Zend\Expressive\Whoops'            => Container\WhoopsFactory::class,
            'Zend\Expressive\WhoopsPageHandler' => Container\WhoopsPageHandlerFactory::class,
        ],
    ],

    'whoops' => [
        'json_exceptions' => [
            'display'    => true,
            'show_trace' => true,
            'ajax_only'  => true,
        ],
    ],
];
