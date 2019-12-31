<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioInstallerTest;

use Mezzio\Container\WhoopsErrorResponseGeneratorFactory;
use Mezzio\Middleware\ErrorResponseGenerator;
use MezzioInstaller\OptionalPackages;

class ErrorHandlerTest extends OptionalPackagesTestCase
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

    /**
     * @runInSeparateProcess
     */
    public function testErrorHandlerIsNotInstalled()
    {
        $this->prepareSandboxForInstallType(OptionalPackages::INSTALL_MINIMAL, $this->installer);

        // Install container
        $config = $this->getInstallerConfig($this->installer);
        $containerResult = $this->installer->processAnswer(
            $config['questions']['container'],
            3
        );
        $this->assertTrue($containerResult);

        // Enable development mode
        $this->enableDevelopmentMode();

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has(ErrorResponseGenerator::class));
        $this->assertFalse($container->has('Mezzio\Whoops'));
        $this->assertFalse($container->has('Mezzio\WhoopsPageHandler'));
    }

    /**
     * @runInSeparateProcess
     *
     * @dataProvider errorHandlerProvider
     */
    public function testErrorHandler(
        string $installType,
        int $containerOption,
        int $errorHandlerOption,
        string $expectedErrorHandler
    ) {
        $this->prepareSandboxForInstallType($installType, $this->installer);
        $config = $this->getInstallerConfig($this->installer);

        // Install container
        $containerResult = $this->installer->processAnswer(
            $config['questions']['container'],
            $containerOption
        );
        $this->assertTrue($containerResult);

        // Install error handler
        $containerResult = $this->installer->processAnswer(
            $config['questions']['error-handler'],
            $errorHandlerOption
        );
        $this->assertTrue($containerResult);

        // Enable development mode
        $this->enableDevelopmentMode();

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has(ErrorResponseGenerator::class));
        $this->assertTrue($container->has('Mezzio\Whoops'));
        $this->assertTrue($container->has('Mezzio\WhoopsPageHandler'));

        $config = $container->get('config');
        $this->assertEquals(
            $expectedErrorHandler,
            $config['dependencies']['factories'][ErrorResponseGenerator::class]
        );
    }

    public function errorHandlerProvider() : array
    {
        // $installType, $containerOption, $errorHandlerOption, $expectedErrorHandler
        return [
            'whoops-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 1, WhoopsErrorResponseGeneratorFactory::class],
            'whoops-full'    => [OptionalPackages::INSTALL_FLAT,    3, 1, WhoopsErrorResponseGeneratorFactory::class],
            'whoops-modular' => [OptionalPackages::INSTALL_MODULAR, 3, 1, WhoopsErrorResponseGeneratorFactory::class],
        ];
    }
}
