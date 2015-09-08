<?php

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

$config = [];
foreach (glob('config/autoload/{{,*.}global,{,*.}local}.php', GLOB_BRACE) as $file) {
    $config = array_replace_recursive($config, include $file);
}

$container = new ServiceManager(new Config($config['dependencies']));
$container->setService('Config', $config);

return $container;
