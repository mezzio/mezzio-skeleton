<?php

return [
    'debug' => false,

    'middleware_pipeline' => [
        'pre_routing' => [],
        'post_routing' => [],
    ],

    'zend-expressive' => [
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],
];
