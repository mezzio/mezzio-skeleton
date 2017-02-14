<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;

class RemoveInstallerTest extends OptionalPackagesTestCase
{
    /**
     * @var OptionalPackages
     */
    protected $installer;

    protected function setUp()
    {
        parent::setUp();
        $this->installer = $this->createOptionalPackages();
    }

    public function testComposerHasInstaller()
    {
        $composer = $this->getComposerDataFromInstaller($this->installer);

        $this->assertTrue(isset($composer['autoload']['psr-4']['ExpressiveInstaller\\']));
        $this->assertTrue(isset($composer['autoload-dev']['psr-4']['ExpressiveInstallerTest\\']));
        $this->assertTrue(isset($composer['extra']['branch-alias']));
        $this->assertFalse(isset($composer['extra']['optional-packages']));
        $this->assertTrue(isset($composer['scripts']['pre-install-cmd']));
        $this->assertTrue(isset($composer['scripts']['pre-update-cmd']));
    }

    public function testInstallerIsRemoved()
    {
        // Remove the installer
        $this->installer->removeInstallerFromDefinition();

        $composer = $this->getComposerDataFromInstaller($this->installer);

        $this->assertFalse(isset($composer['autoload']['psr-4']['ExpressiveInstaller\\']));
        $this->assertFalse(isset($composer['autoload-dev']['psr-4']['ExpressiveInstallerTest\\']));
        $this->assertFalse(isset($composer['extra']['branch-alias']));
        $this->assertFalse(isset($composer['extra']['optional-packages']));
        $this->assertFalse(isset($composer['scripts']['pre-install-cmd']));
        $this->assertFalse(isset($composer['scripts']['pre-update-cmd']));
    }
}
