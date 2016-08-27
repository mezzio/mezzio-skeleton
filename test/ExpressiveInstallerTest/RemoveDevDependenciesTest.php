<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use ReflectionMethod;

class RemoveDevDependenciesTest extends InstallerTestCase
{
    /**
     * @dataProvider packageProvider
     */
    public function testDevDependenciesRemoval($package)
    {
        $this->assertTrue($this->composerRequires($package));

        $method = new ReflectionMethod(OptionalPackages::class, 'removeDevDependencies');
        $method->setAccessible(true);
        $method->invoke(OptionalPackages::class);

        $this->assertFalse($this->composerRequires($package));
    }

    public function packageProvider()
    {
        // $package
        return [
            'aura-di'                          => ['aura/di'],
            'composer'                         => ['composer/composer'],
            'pimple-container-interop'         => ['xtreamwayz/pimple-container-interop'],
            'whoops'                           => ['filp/whoops'],
            'zend-expressive-aurarouter'       => ['zendframework/zend-expressive-aurarouter'],
            'zend-expressive-fastroute'        => ['zendframework/zend-expressive-fastroute'],
            'zend-expressive-platesrenderer'   => ['zendframework/zend-expressive-platesrenderer'],
            'zend-expressive-twigrenderer'     => ['zendframework/zend-expressive-twigrenderer'],
            'zend-expressive-zendrouter'       => ['zendframework/zend-expressive-zendrouter'],
            'zend-expressive-zendviewrenderer' => ['zendframework/zend-expressive-zendviewrenderer'],
            'zend-servicemanager'              => ['zendframework/zend-servicemanager'],
        ];
    }
}
