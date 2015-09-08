<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

class HomePageFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new HomePageAction($container);
    }
}
