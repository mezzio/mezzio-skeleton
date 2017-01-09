<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use ReflectionProperty;

class RemoveInstallerTest extends InstallerTestCase
{
    public function testComposerHasInstaller()
    {
        $config = $this->getComposerDefinition();

        $this->assertTrue(isset($config['autoload']['psr-4']['ExpressiveInstaller\\']));
        $this->assertTrue(isset($config['autoload-dev']['psr-4']['ExpressiveInstallerTest\\']));
        $this->assertTrue(isset($config['extra']['branch-alias']));
        $this->assertFalse(isset($config['extra']['optional-packages']));
        $this->assertTrue(isset($config['scripts']['pre-install-cmd']));
        $this->assertTrue(isset($config['scripts']['pre-update-cmd']));
    }

    public function testInstallerIsRemoved()
    {
        // Remove the installer
        OptionalPackages::removeInstallerFromDefinition();

        $config = $this->getComposerDefinition();

        $this->assertFalse(isset($config['autoload']['psr-4']['ExpressiveInstaller\\']));
        $this->assertFalse(isset($config['autoload-dev']['psr-4']['ExpressiveInstallerTest\\']));
        $this->assertFalse(isset($config['extra']['branch-alias']));
        $this->assertFalse(isset($config['extra']['optional-packages']));
        $this->assertFalse(isset($config['scripts']['pre-install-cmd']));
        $this->assertFalse(isset($config['scripts']['pre-update-cmd']));
    }

    public function testInstallerDataIsRemoved()
    {
        // Mimic answered question
        $refDefinition = new ReflectionProperty(OptionalPackages::class, 'composerDefinition');
        $refDefinition->setAccessible(true);
        $definition = $refDefinition->getValue();
        $definition['extra']['optional-packages']['router'] = 3;
        $refDefinition->setValue($definition);

        // Test if the value is stored
        $definition = $this->getComposerDefinition();
        $this->assertTrue(isset($definition['extra']['optional-packages']));

        // Remove the installer
        OptionalPackages::removeInstallerFromDefinition();

        // Test if the value is removed
        $definition = $this->getComposerDefinition();
        $this->assertFalse(isset($definition['extra']['optional-packages']));
        $this->assertFalse(isset($definition['extra']));
    }
}
