<?php

declare(strict_types=1);

use Laminas\AuraDi\Config\Config;
use Laminas\AuraDi\Config\ContainerFactory;

$config  = require __DIR__ . '/config.php';
$factory = new ContainerFactory();

return $factory(new Config($config));
