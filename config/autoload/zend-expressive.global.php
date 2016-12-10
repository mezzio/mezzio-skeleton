<?php

return [
    'debug' => false,

    'zend-expressive' => [
        // Enable exception-based error handling via standard middleware.
        'raise_throwables' => true,

        // Enable programmatic pipeline: Any `middleware_pipeline` or `routes`
        // configuration will be ignored when creating the `Application` instance.
        'programmatic_pipeline' => true,

        // Provide templates for the error handling middleware to use when
        // generating responses.
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],
];
