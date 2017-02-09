<?php

use App\ExpressiveAuraConfig;
use App\ExpressiveAuraDelegatorFactory;
use Aura\Di\ContainerBuilder;

require_once __DIR__ . '/ExpressiveAuraConfig.php';
require_once __DIR__ . '/ExpressiveAuraDelegatorFactory.php';

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$builder = new ContainerBuilder();
return $builder->newConfiguredInstance([
    new ExpressiveAuraConfig($config),
]);
