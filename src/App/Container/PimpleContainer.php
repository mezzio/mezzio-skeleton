<?php

namespace App\Container;

use Pimple\Container as Pimple;
use Interop\Container\ContainerInterface;

/**
 * ContainerInterface wrapper for Pimple 3.0
 *
 * @package App\Container
 */
class PimpleContainer extends Pimple implements ContainerInterface
{
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }
}
