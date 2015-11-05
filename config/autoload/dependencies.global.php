<?php

return [
    // Provides services for whole application
    // It's recommend using fully-qualified class names whenever possible as
    // service names
    'dependencies' => [
        // Use 'invokables' for constructor-less services
        // It tells to the container what class to instantiate when a particular
        // service is requested
        'invokables' => [
            //Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks
        'factories' => [
            Zend\Expressive\Application::class => Zend\Expressive\Container\ApplicationFactory::class,
        ]
    ]
];
