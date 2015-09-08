<?php

use Aura\Di\ContainerBuilder;
use Zend\Config\Config;

// Load configuration
$config = [];
foreach (glob('config/autoload/{{,*.}global,{,*.}local}.php', GLOB_BRACE) as $file) {
    $config = array_replace_recursive($config, include $file);
}

// Build container
$builder = new ContainerBuilder();
$container = $builder->newInstance();

// Inject config as a service
$container->set('config', new Config($config));

// Inject factories
foreach ($config['dependencies']['factories'] as $name => $object) {
    $container->set($object, $container->lazyNew($object));
    $container->set($name, $container->lazyGetCall($object, '__invoke', $container));
}

// Inject invokables
foreach ($config['dependencies']['invokables'] as $name => $object) {
    $container->set($name, $container->lazyNew($object));
}

return $container;
