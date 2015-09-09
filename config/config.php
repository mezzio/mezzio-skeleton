<?php

use Zend\Config\Config;
use Zend\Config\Factory;
use Zend\Config\Writer\PhpArray;

$env = getenv('APP_ENV') ?: 'development';
$cachedConfigFile = 'data/cache/app_config.php';

// Try to load the cached config
if ($env === 'production' && is_file($cachedConfigFile)) {
    return new Config(require $cachedConfigFile);
}

// Merge configuration files. Load *.global.php first so *.local.php can overwrite.
$config = Factory::fromFiles(
    glob('config/autoload/{{,*.}global,{,*.}local}.php', GLOB_BRACE)
);

// Cache config if in production
if ($env === 'production') {
    $writer = new PhpArray();
    $writer->setUseBracketArraySyntax(true);
    $writer->toFile($cachedConfigFile, $config);
}

return new Config($config);
