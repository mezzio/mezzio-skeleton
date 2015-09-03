<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

class PingFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PingAction($container);
    }
}
