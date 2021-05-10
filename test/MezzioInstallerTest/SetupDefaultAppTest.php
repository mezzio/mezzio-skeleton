<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;

use function chdir;

class SetupDefaultAppTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /** @var OptionalPackages */
    private $installer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = $this->copyProjectFilesToTempFilesystem();
        $this->installer   = $this->createOptionalPackages($this->projectRoot);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        chdir($this->packageRoot);
        $this->recursiveDelete($this->projectRoot);
        $this->tearDownAlternateAutoloader();
    }

    public function testModularInstallationAddsToolingSupportAsDevRequirement()
    {
        $this->prepareSandboxForInstallType(OptionalPackages::INSTALL_MODULAR, $this->installer);
        self::assertPackage(
            'mezzio/mezzio-tooling',
            $this->installer,
            'mezzio-tooling package was not injected into composer.json'
        );
    }
}
