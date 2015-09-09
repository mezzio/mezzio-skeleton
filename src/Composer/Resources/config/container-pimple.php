<?php

use App\Container\PimpleContainer;

// Load configuration
$config = require 'config.php';

// Build container
$container = new PimpleContainer();

// Inject config
$container['config'] = $config;

// Inject factories
foreach ($config['dependencies']['factories'] as $name => $object) {
    $container[$name] = function ($c) use ($object) {
        $factory = new $object();
        return $factory($c);
    };
}

// Inject invokables
foreach ($config['dependencies']['invokables'] as $name => $object) {
    $container[$name] = function ($c) use ($object) {
        return new $object();
    };
}

return $container;
