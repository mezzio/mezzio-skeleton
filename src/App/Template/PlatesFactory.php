<?php

namespace App\Template;

use Interop\Container\ContainerInterface;
use League\Plates\Engine as PlatesEngine;
use Zend\Expressive\Template\Plates;

class PlatesFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        // Create the engine instance:
        $engine = new PlatesEngine();

        // Inject engine
        $plates = new Plates($engine);

        // Add template paths
        foreach ($config['templates']['paths'] as $path => $namespace) {
            $plates->addPath($path, $namespace);
        }

        return $plates;
    }
}
