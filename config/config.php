<?php

use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
    'config_cache_path' => 'data/config-cache.php',
];

$aggregator = new ConfigAggregator(
    [
        //Include cache config for zf-development-mode
        function () use ($cacheConfig) {
            return $cacheConfig;
        },

        // Load module config
        new PhpFileProvider('src/App/Config/{{,*.}global}.php'),

        // Load application config in a pre-defined order in such a way that local settings
        // overwrite global settings. (Loaded as first to last):
        //   - `global.php`
        //   - `*.global.php`
        //   - `local.php`
        //   - `*.local.php`
        new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),

        // Load development config if it exists
        new PhpFileProvider('config/development.config.php'),
    ],
    // Cached config file
    $cacheConfig['config_cache_path']
);

return $aggregator->getMergedConfig();
