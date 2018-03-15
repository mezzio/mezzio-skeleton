<?php

declare(strict_types=1);

use Zend\Pimple\Config\Config;
use Zend\Pimple\Config\ContainerFactory;

$config  = require __DIR__ . '/config.php';
$factory = new ContainerFactory();

return $factory(new Config($config));
