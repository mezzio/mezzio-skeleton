<?php

declare(strict_types=1);

use Laminas\Pimple\Config\Config;
use Laminas\Pimple\Config\ContainerFactory;

$config  = require __DIR__ . '/config.php';
$factory = new ContainerFactory();

return $factory(new Config($config));
