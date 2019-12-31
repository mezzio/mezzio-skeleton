<?php

use Laminas\View\HelperPluginManager;
use Mezzio\LaminasView\HelperPluginManagerFactory;
use Mezzio\LaminasView\LaminasViewRendererFactory;
use Mezzio\Template\TemplateRendererInterface;

return [
    'dependencies' => [
        'factories' => [
            TemplateRendererInterface::class => LaminasViewRendererFactory::class,
            HelperPluginManager::class => HelperPluginManagerFactory::class,
        ],
    ],

    'templates' => [
        'layout' => 'layout::default',
    ],

    'view_helpers' => [
        // laminas-servicemanager-style configuration for adding view helpers:
        // - 'aliases'
        // - 'invokables'
        // - 'factories'
        // - 'abstract_factories'
        // - etc.
    ],
];
