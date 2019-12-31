<?php

declare(strict_types=1);

use Northwoods\Container\Config;
use Northwoods\Container\InjectorBuilder;
use Psr\Container\ContainerInterface;

// Load configuration
$config = require __DIR__ . '/config.php';

$builder = new InjectorBuilder([
    new Config\ContainerConfig(),
    new Config\ServiceConfig($config['dependencies'] ?? []),
]);

/** @var \Auryn\Injector */
$injector = $builder->build();

$injector->share('config')->delegate('config', function () use ($config) : \ArrayObject {
    // Auryn requires that all injections resolve to an object.
    // Wrap the config array with an ArrayObject to appease the gods.
    return new \ArrayObject($config);
});

return $injector->make(ContainerInterface::class);
