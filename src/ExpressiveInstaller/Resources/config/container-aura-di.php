<?php

use App\ExpressiveAuraConfig;
use Aura\Di\ContainerBuilder;

require_once __DIR__ . '/ExpressiveAuraConfig.php';
require_once __DIR__ . '/ExpressiveAuraDelegatorFactory.php';

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$builder = new ContainerBuilder();
return $builder->newConfiguredInstance([
    new ExpressiveAuraConfig(is_array($config) ? $config : []),
]);
