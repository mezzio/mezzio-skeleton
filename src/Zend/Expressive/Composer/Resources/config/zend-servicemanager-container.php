<?php

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

// Load configuration
$config = [];
foreach (glob('config/autoload/{{,*.}global,{,*.}local}.php', GLOB_BRACE) as $file) {
    $config = array_replace_recursive($config, include $file);
}

// Build container
$container = new ServiceManager(new Config($config['dependencies']));

// Inject config as a service
$container->setService('config', $config);

return $container;
