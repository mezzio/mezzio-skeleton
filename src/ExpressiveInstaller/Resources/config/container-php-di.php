<?php

use Elie\PHPDI\Config\Config;
use Elie\PHPDI\Config\ContainerFactory;

// Protect variables from global scope
return call_user_func(function () {

    $config = require __DIR__ . '/config.php';

    $factory = new ContainerFactory();

    return $factory(new Config($config));
});
