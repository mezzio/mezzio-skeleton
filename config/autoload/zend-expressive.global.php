<?php

return [
    'debug' => false,

    'zend-expressive' => [
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::500',
        ],
    ],
];
