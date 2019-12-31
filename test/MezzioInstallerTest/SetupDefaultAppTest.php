<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;

class SetupDefaultAppTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /**
     * @var OptionalPackages
     */
    private $installer;

    protected function setUp()
    {
        parent::setUp();
        $this->projectRoot = $this->copyProjectFilesToTempFilesystem();
        $this->installer   = $this->createOptionalPackages($this->projectRoot);
    }

    protected function tearDown()
    {
        parent::tearDown();
        chdir($this->packageRoot);
        $this->recursiveDelete($this->projectRoot);
        $this->tearDownAlternateAutoloader();
    }

    public function testModularInstallationAddsToolingSupportAsDevRequirement()
    {
        $this->prepareSandboxForInstallType(OptionalPackages::INSTALL_MODULAR, $this->installer);
        $this->assertPackage(
            'mezzio/mezzio-tooling',
            $this->installer,
            'mezzio-tooling package was not injected into composer.json'
        );
    }

    public function testFlatInstallationDoesNotAddToolingSupport()
    {
        $this->prepareSandboxForInstallType(OptionalPackages::INSTALL_FLAT, $this->installer);
        $this->assertNotPackage(
            'mezzio/mezzio-tooling',
            $this->installer,
            'mezzio-tooling package WAS injected into composer.json, but should not have been'
        );
    }
}
