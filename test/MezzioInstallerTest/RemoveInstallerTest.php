<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use ReflectionProperty;

class RemoveInstallerTest extends InstallerTestCase
{
    public function testComposerHasInstaller()
    {
        $config = $this->getComposerDefinition();

        $this->assertTrue(isset($config['autoload']['psr-4']['MezzioInstaller\\']));
        $this->assertTrue(isset($config['autoload-dev']['psr-4']['MezzioInstallerTest\\']));
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

        $this->assertFalse(isset($config['autoload']['psr-4']['MezzioInstaller\\']));
        $this->assertFalse(isset($config['autoload-dev']['psr-4']['MezzioInstallerTest\\']));
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
