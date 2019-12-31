<?php

use Mezzio\Plates\PlatesRendererFactory;
use Mezzio\Template\TemplateRendererInterface;

return [
    'dependencies' => [
        'factories' => [
            TemplateRendererInterface::class => PlatesRendererFactory::class,
        ],
    ],

    'templates' => [
        'extension' => 'phtml',
    ],
];
