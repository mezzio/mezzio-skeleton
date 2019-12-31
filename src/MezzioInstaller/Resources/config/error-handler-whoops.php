<?php

return [
    'dependencies' => [
        'invokables' => [
            'Mezzio\Whoops' => Whoops\Run::class,
            'Mezzio\WhoopsPageHandler' => Whoops\Handler\PrettyPageHandler::class,
        ],
        'factories' => [
            'Mezzio\FinalHandler' => Mezzio\Container\WhoopsErrorHandlerFactory::class,
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
