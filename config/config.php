<?php

use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

$aggregator = new ConfigAggregator(
    [
        // Load module config
        new PhpFileProvider('src/App/Config/{{,*.}global}.php'),

        // Load application config in a pre-defined order in such a way that local settings
        // overwrite global settings. (Loaded as first to last):
        //   - `global.php`
        //   - `*.global.php`
        //   - `local.php`
        //   - `*.local.php`
        new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
    ],
    // Cached config file
    'data/config-cache.php'
);

return $aggregator->getMergedConfig();
