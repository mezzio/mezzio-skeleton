<?php

use Northwoods\Container\InjectorBuilder;
use Northwoods\Container\Config;
use Psr\Container\ContainerInterface;

// Load configuration
$config = require __DIR__ . '/config.php';

$builder = new InjectorBuilder([
    new Config\ContainerConfig(),
    new Config\ServiceConfig(isset($config['dependencies']) ? $config['dependencies'] : []),
]);

/** @var \Auryn\Injector */
$injector = $builder->build();

$injector->share('config')->delegate('config', function () use ($config) {
    // Auryn requires that all injections resolve to an object.
    // Wrap the config array with an ArrayObject to appease the gods.
    return new ArrayObject($config);
});

return $injector->make(ContainerInterface::class);
