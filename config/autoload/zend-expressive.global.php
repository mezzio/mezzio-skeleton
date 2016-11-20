<?php

return [
    'debug' => false,

    // Cache the configuration. Recommended for production.
    'config_cache_enabled' => false,

    'zend-expressive' => [
        // Enable the new error handling.
        'raise_throwables' => true,
        // Enable programmatic pipeline: Any `middleware_pipeline` or `routes`
        // configuration will be ignored when creating the `Application` instance.
        'programmatic_pipeline' => true,
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],
];
