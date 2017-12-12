<?php

use Zend\AuraDi\Config\Config;
use Zend\AuraDi\Config\ContainerFactory;

$config  = require __DIR__ . '/config.php';
$factory = new ContainerFactory();

return $factory(new Config($config));
