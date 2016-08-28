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

class RemoveInstallerTest extends InstallerTestCase
{
    public function testComposerHasInstaller()
    {
        $config = $this->getComposerDefinition();

        $this->assertTrue(isset($config['autoload']['psr-4']['ExpressiveInstaller\\']));
        $this->assertTrue(isset($config['autoload-dev']['psr-4']['ExpressiveInstallerTest\\']));
        $this->assertFalse(isset($config['extra']['optional-packages']));
        $this->assertTrue(isset($config['scripts']['pre-install-cmd']));
        $this->assertTrue(isset($config['scripts']['pre-update-cmd']));
    }

    public function testInstallerIsRemoved()
    {
        $method = new ReflectionMethod(OptionalPackages::class, 'removeInstallerFromDefinition');
        $method->setAccessible(true);
        $method->invoke(OptionalPackages::class);

        $config = $this->getComposerDefinition();

        $this->assertFalse(isset($config['autoload']['psr-4']['ExpressiveInstaller\\']));
        $this->assertFalse(isset($config['autoload-dev']['psr-4']['ExpressiveInstallerTest\\']));
        $this->assertFalse(isset($config['extra']['optional-packages']));
        $this->assertFalse(isset($config['scripts']['pre-install-cmd']));
        $this->assertFalse(isset($config['scripts']['pre-update-cmd']));
    }
}
