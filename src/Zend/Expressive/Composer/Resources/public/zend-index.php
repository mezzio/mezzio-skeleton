<?php

namespace App;

use Zend\ServiceManager\ServiceManager as Container;

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

opcache_reset();

chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';

$config = require 'config/services.php';
$container = new Container($config);

/** @var \Zend\Expressive\Application $app */
$app = $container->get('Zend\Expressive\Application');
$app->run();
