<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;

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
            'zendframework/zend-expressive-tooling',
            $this->installer,
            'zend-expressive-tooling package was not injected into composer.json'
        );
    }
}
