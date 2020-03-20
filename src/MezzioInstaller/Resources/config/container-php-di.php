<?php

declare(strict_types=1);

use Elie\PHPDI\Config\Config;
use Elie\PHPDI\Config\ContainerFactory;

// Protect variables from global scope
return (static function () {
    $config  = require __DIR__ . '/config.php';
    $factory = new ContainerFactory();

    return $factory(new Config($config));
})();
