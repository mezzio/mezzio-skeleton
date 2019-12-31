<?php

use App\MezzioAuraConfig;
use Aura\Di\ContainerBuilder;

require_once __DIR__ . '/MezzioAuraConfig.php';
require_once __DIR__ . '/MezzioAuraDelegatorFactory.php';

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$builder = new ContainerBuilder();
return $builder->newConfiguredInstance([
    new MezzioAuraConfig(is_array($config) ? $config : []),
]);
