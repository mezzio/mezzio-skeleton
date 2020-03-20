<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;

class RemoveInstallerTest extends OptionalPackagesTestCase
{
    /**
     * @var OptionalPackages
     */
    private $installer;

    protected function setUp() : void
    {
        parent::setUp();
        $this->installer = $this->createOptionalPackages();
    }

    public function testComposerHasInstaller()
    {
        $composer = $this->getComposerDataFromInstaller($this->installer);

        $this->assertTrue(isset($composer['autoload']['psr-4']['MezzioInstaller\\']));
        $this->assertTrue(isset($composer['autoload-dev']['psr-4']['MezzioInstallerTest\\']));
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

        $this->assertFalse(isset($composer['autoload']['psr-4']['MezzioInstaller\\']));
        $this->assertFalse(isset($composer['autoload-dev']['psr-4']['MezzioInstallerTest\\']));
        $this->assertFalse(isset($composer['extra']['branch-alias']));
        $this->assertFalse(isset($composer['extra']['optional-packages']));
        $this->assertFalse(isset($composer['scripts']['pre-install-cmd']));
        $this->assertFalse(isset($composer['scripts']['pre-update-cmd']));
        $this->assertFalse(isset($composer['scripts']['check']['@analyze']));
        $this->assertFalse(isset($composer['scripts']['analyze']));
    }
}
