<?php

use Xtreamwayz\Pimple\Container;

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$container = new Container();

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
