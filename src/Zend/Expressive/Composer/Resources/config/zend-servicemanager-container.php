<?php

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;
use Zend\Config\Factory as ConfigFactory;

$config = ConfigFactory::fromFiles(
    glob('config/autoload/{{,*.}global,{,*.}local}.php', GLOB_BRACE)
);

if (isset($config['strict_php'])) {
    StrictPhp\StrictPhpKernel::getInstance()->init($config['strict_php']);
}

$container = new ServiceManager(new Config($config['dependencies']));
$container->setService('Config', $config);

return $container;
