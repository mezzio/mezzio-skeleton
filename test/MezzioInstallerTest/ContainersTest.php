<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use Chubbyphp\Container\MinimalContainer as ChubbyphpMinimalContainer;
use DI\Container as PhpDIContainer;
use Laminas\ServiceManager\ServiceManager as LaminasManagerContainer;
use Mezzio\Application;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouterInterface;
use MezzioInstaller\OptionalPackages;
use Pimple\Psr11\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;

use function chdir;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
use function str_starts_with;

use const DIRECTORY_SEPARATOR;

class ContainersTest extends OptionalPackagesTestCase
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

    /**
     * @runInSeparateProcess
     * @dataProvider containerProvider
     * @psalm-param OptionalPackages::INSTALL_* $installType
     * @psalm-param class-string<ContainerInterface> $expectedContainer
     * @psalm-param 'minimal-files'|'copy-files' $copyFilesKey
     */
    public function testContainer(
        string $installType,
        int $containerOption,
        int $routerOption,
        string $copyFilesKey,
        int $expectedResponseStatusCode,
        string $expectedContainer
    ): void {
        $this->prepareSandboxForInstallType($installType, $this->installer);

        // Install container
        $config          = $this->getInstallerConfig($this->installer);
        $containerResult = $this->installer->processAnswer(
            $config['questions']['container'],
            $containerOption
        );
        self::assertTrue($containerResult);

        // Install router
        $routerResult = $this->installer->processAnswer(
            $config['questions']['router'],
            $routerOption
        );
        self::assertTrue($routerResult);

        $configFile         = $this->projectRoot . DIRECTORY_SEPARATOR . '/config/config.php';
        $configFileContents = file_get_contents($configFile);
        $configFileContents = preg_replace(
            '#(new ConfigAggregator\(\[)#s',
            '$1
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
',
            $configFileContents
        );
        file_put_contents($configFile, $configFileContents);

        // Test container
        $container = $this->getContainer();
        self::assertInstanceOf(ContainerInterface::class, $container);
        self::assertInstanceOf($expectedContainer, $container);
        self::assertTrue($container->has(UrlHelper::class));
        self::assertTrue($container->has(ServerUrlHelper::class));
        self::assertTrue($container->has(Application::class));
        self::assertTrue($container->has(RouterInterface::class));

        // Test home page
        $setupRoutes = ! str_starts_with($copyFilesKey, 'minimal');
        $response    = $this->getAppResponse('/', $setupRoutes);
        self::assertEquals($expectedResponseStatusCode, $response->getStatusCode());
    }

    /**
     * @psalm-return array<string, array{
     *     0: OptionalPackages::INSTALL_*,
     *     1: int,
     *     2: int,
     *     3: 'minimal-files'|'copy-files',
     *     4: int,
     *     5: class-string<ContainerInterface>
     * }>
     */
    public function containerProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        // $installType, $containerOption, $routerOption, $copyFilesKey, $expectedResponseStatusCode, $expectedContainer
        return [
            'pimple-minimal'      => [OptionalPackages::INSTALL_MINIMAL, 1, 2, 'minimal-files', 404, PimpleContainer::class],
            'pimple-flat'         => [OptionalPackages::INSTALL_FLAT,    1, 2, 'copy-files', 200, PimpleContainer::class],
            'pimple-modular'      => [OptionalPackages::INSTALL_MODULAR, 1, 2, 'copy-files', 200, PimpleContainer::class],
            'laminas-sm-minimal'  => [OptionalPackages::INSTALL_MINIMAL, 2, 2, 'minimal-files', 404, LaminasManagerContainer::class],
            'laminas-sm-flat'     => [OptionalPackages::INSTALL_FLAT,    2, 2, 'copy-files', 200, LaminasManagerContainer::class],
            'laminas-sm-modular'  => [OptionalPackages::INSTALL_MODULAR, 2, 2, 'copy-files', 200, LaminasManagerContainer::class],
            'sf-di-minimal'       => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 'minimal-files', 404, SfContainerBuilder::class],
            'sf-di-flat'          => [OptionalPackages::INSTALL_FLAT,    3, 2, 'copy-files', 200, SfContainerBuilder::class],
            'sf-di-modular'       => [OptionalPackages::INSTALL_MODULAR, 3, 2, 'copy-files', 200, SfContainerBuilder::class],
            'php-di-minimal'      => [OptionalPackages::INSTALL_MINIMAL, 4, 2, 'minimal-files', 404, PhpDIContainer::class],
            'php-di-flat'         => [OptionalPackages::INSTALL_FLAT,    4, 2, 'copy-files', 200, PhpDIContainer::class],
            'php-di-modular'      => [OptionalPackages::INSTALL_MODULAR, 4, 2, 'copy-files', 200, PhpDIContainer::class],
            'chubbyphp-c-minimal' => [OptionalPackages::INSTALL_MINIMAL, 5, 2, 'minimal-files', 404, ChubbyphpMinimalContainer::class],
            'chubbyphp-c-flat'    => [OptionalPackages::INSTALL_FLAT,    5, 2, 'copy-files', 200, ChubbyphpMinimalContainer::class],
            'chubbyphp-c-modular' => [OptionalPackages::INSTALL_MODULAR, 5, 2, 'copy-files', 200, ChubbyphpMinimalContainer::class],
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
