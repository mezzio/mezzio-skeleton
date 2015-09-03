<?php

namespace App;

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

opcache_reset();

chdir(dirname(__DIR__));
require __DIR__ . '/../vendor/autoload.php';

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';

/** @var \Zend\Expressive\Application $app */
$app = $container->get('Zend\Expressive\Application');
$app->run();

dump(number_format((float) (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 4, '.', ''));
