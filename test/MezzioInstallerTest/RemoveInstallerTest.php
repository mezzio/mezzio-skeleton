<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;

class RemoveInstallerTest extends OptionalPackagesTestCase
{
    /** @var OptionalPackages */
    private $installer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->installer = $this->createOptionalPackages();
    }

    public function testComposerHasInstaller()
    {
        $composer = $this->getComposerDataFromInstaller($this->installer);

        self::assertTrue(isset($composer['autoload']['psr-4']['MezzioInstaller\\']));
        self::assertTrue(isset($composer['autoload-dev']['psr-4']['MezzioInstallerTest\\']));
        self::assertFalse(isset($composer['extra']['optional-packages']));
        self::assertTrue(isset($composer['scripts']['pre-install-cmd']));
        self::assertTrue(isset($composer['scripts']['pre-update-cmd']));
    }

    public function testInstallerIsRemoved()
    {
        // Remove the installer
        $this->installer->removeInstallerFromDefinition();

        $composer = $this->getComposerDataFromInstaller($this->installer);

        self::assertFalse(isset($composer['autoload']['psr-4']['MezzioInstaller\\']));
        self::assertFalse(isset($composer['autoload-dev']['psr-4']['MezzioInstallerTest\\']));
        self::assertFalse(isset($composer['extra']['optional-packages']));
        self::assertFalse(isset($composer['scripts']['pre-install-cmd']));
        self::assertFalse(isset($composer['scripts']['pre-update-cmd']));
        self::assertFalse(isset($composer['scripts']['check']['@analyze']));
        self::assertFalse(isset($composer['scripts']['analyze']));
    }
}
