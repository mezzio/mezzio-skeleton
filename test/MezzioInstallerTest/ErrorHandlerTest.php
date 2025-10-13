<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use Mezzio\Container\WhoopsErrorResponseGeneratorFactory;
use Mezzio\Middleware\ErrorResponseGenerator;
use MezzioInstaller\OptionalPackages;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

use function chdir;

final class ErrorHandlerTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    private OptionalPackages $installer;

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

    #[RunInSeparateProcess]
    public function testErrorHandlerIsNotInstalled(): void
    {
        $this->prepareSandboxForInstallType(OptionalPackages::INSTALL_MINIMAL, $this->installer);

        // Install container
        $config          = $this->getInstallerConfig($this->installer);
        $containerResult = $this->installer->processAnswer(
            $config['questions']['container'],
            3
        );
        self::assertTrue($containerResult);

        // Enable development mode
        $this->enableDevelopmentMode();

        // Test container
        $container = $this->getContainer();
        self::assertTrue($container->has(ErrorResponseGenerator::class));
        self::assertFalse($container->has('Mezzio\Whoops'));
        self::assertFalse($container->has('Mezzio\WhoopsPageHandler'));
    }

    #[DataProvider('errorHandlerProvider')]
    #[RunInSeparateProcess]
    public function testErrorHandler(
        string $installType,
        int $containerOption,
        int $errorHandlerOption,
        string $expectedErrorHandler
    ): void {
        $this->prepareSandboxForInstallType($installType, $this->installer);
        $config = $this->getInstallerConfig($this->installer);

        // Install container
        $containerResult = $this->installer->processAnswer(
            $config['questions']['container'],
            $containerOption
        );
        self::assertTrue($containerResult);

        // Install error handler
        $containerResult = $this->installer->processAnswer(
            $config['questions']['error-handler'],
            $errorHandlerOption
        );
        self::assertTrue($containerResult);

        // Enable development mode
        $this->enableDevelopmentMode();

        // Test container
        $container = $this->getContainer();
        self::assertTrue($container->has(ErrorResponseGenerator::class));
        self::assertTrue($container->has('Mezzio\Whoops'));
        self::assertTrue($container->has('Mezzio\WhoopsPageHandler'));

        $config = $container->get('config');
        self::assertEquals(
            $expectedErrorHandler,
            $config['dependencies']['factories'][ErrorResponseGenerator::class]
        );
    }

    /**
     * @return array<string, array{
     *     0: OptionalPackages::INSTALL_*,
     *     1: int,
     *     2: int,
     *     3: class-string,
     * }>
     */
    public static function errorHandlerProvider(): array
    {
        // $installType, $containerOption, $errorHandlerOption, $expectedErrorHandler
        return [
            'whoops-minimal' => [OptionalPackages::INSTALL_MINIMAL, 2, 1, WhoopsErrorResponseGeneratorFactory::class],
            'whoops-full'    => [OptionalPackages::INSTALL_FLAT,    2, 1, WhoopsErrorResponseGeneratorFactory::class],
            'whoops-modular' => [OptionalPackages::INSTALL_MODULAR, 2, 1, WhoopsErrorResponseGeneratorFactory::class],
        ];
    }
}
