<?php

return [
    'debug' => false,

    'config_cache_enabled' => false,

    'mezzio' => [
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],
];
