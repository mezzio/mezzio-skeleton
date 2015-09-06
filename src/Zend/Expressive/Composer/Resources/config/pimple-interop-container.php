<?php

use Interop\Container\Pimple\PimpleInterop as Container;
use Zend\Config\Config;

// Load configuration
$config = [];
foreach (glob('config/autoload/{{,*.}global,{,*.}local}.php', GLOB_BRACE) as $file) {
    $config = array_replace_recursive($config, include $file);
}

// Build container
$container = new Container();

// Inject config as a service
$container['config'] = $config;

// Inject factories
foreach ($config['dependencies']['factories'] as $name => $object) {
    $container[$name] = $container->share(function ($c) use ($object) {
        $factory = new $object();
        return $factory($c);
    });
}

// Inject invokables
foreach ($config['dependencies']['invokables'] as $name => $object) {
    $container[$name] = $container->share(function ($c) use ($object) {
        return new $object();
    });
}

return $container;
