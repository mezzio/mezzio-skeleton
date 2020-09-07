<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioInstallerTest;

use Aura\Di\Container as AuraContainer;
use Chubbyphp\Container\Container as ChubbyphpContainer;
use DI\Container as PhpDIContainer;
use Laminas\ServiceManager\ServiceManager as LaminasManagerContainer;
use Mezzio;
use MezzioInstaller\OptionalPackages;
use Northwoods\Container\InjectorContainer as AurynContainer;
use Pimple\Psr11\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;

use function chdir;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
use function strpos;

use const DIRECTORY_SEPARATOR;

class ContainersTest extends OptionalPackagesTestCase
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

    /**
     * @runInSeparateProcess
     * @dataProvider containerProvider
     */
    public function testContainer(
        string $installType,
        int $containerOption,
        int $routerOption,
        string $copyFilesKey,
        int $expectedResponseStatusCode,
        string $expectedContainer
    ) {
        $this->prepareSandboxForInstallType($installType, $this->installer);

        // Install container
        $config = $this->getInstallerConfig($this->installer);
        $containerResult = $this->installer->processAnswer(
            $config['questions']['container'],
            $containerOption
        );
        $this->assertTrue($containerResult);

        // Install router
        $routerResult = $this->installer->processAnswer(
            $config['questions']['router'],
            $routerOption
        );
        $this->assertTrue($routerResult);

        $configFile = $this->projectRoot . DIRECTORY_SEPARATOR . '/config/config.php';
        $configFileContents = file_get_contents($configFile);
        $configFileContents = preg_replace(
            '/(new ConfigAggregator\(\[)/s',
            '$1' . "\n    \Mezzio\\Router\\FastRouteRouter\ConfigProvider::class,\n",
            $configFileContents
        );
        file_put_contents($configFile, $configFileContents);

        // Test container
        $container = $this->getContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertInstanceOf($expectedContainer, $container);
        $this->assertTrue($container->has(Mezzio\Helper\UrlHelper::class));
        $this->assertTrue($container->has(Mezzio\Helper\ServerUrlHelper::class));
        $this->assertTrue($container->has(Mezzio\Application::class));
        $this->assertTrue($container->has(Mezzio\Router\RouterInterface::class));

        // Test home page
        $setupRoutes = strpos($copyFilesKey, 'minimal') !== 0;
        $response    = $this->getAppResponse('/', $setupRoutes);
        $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
    }

    public function containerProvider(): array
    {
        // @codingStandardsIgnoreStart
        // $installType, $containerOption, $routerOption, $copyFilesKey, $expectedResponseStatusCode, $expectedContainer
        return [
            'aura-minimal'         => [OptionalPackages::INSTALL_MINIMAL, 1, 2, 'minimal-files', 404, AuraContainer::class],
            'aura-flat'            => [OptionalPackages::INSTALL_FLAT,    1, 2, 'copy-files', 200, AuraContainer::class],
            'aura-modular'         => [OptionalPackages::INSTALL_MODULAR, 1, 2, 'copy-files', 200, AuraContainer::class],
            'pimple-minimal'       => [OptionalPackages::INSTALL_MINIMAL, 2, 2, 'minimal-files', 404, PimpleContainer::class],
            'pimple-flat'          => [OptionalPackages::INSTALL_FLAT,    2, 2, 'copy-files', 200, PimpleContainer::class],
            'pimple-modular'       => [OptionalPackages::INSTALL_MODULAR, 2, 2, 'copy-files', 200, PimpleContainer::class],
            'laminas-sm-minimal'   => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 'minimal-files', 404, LaminasManagerContainer::class],
            'laminas-sm-flat'      => [OptionalPackages::INSTALL_FLAT,    3, 2, 'copy-files', 200, LaminasManagerContainer::class],
            'laminas-sm-modular'   => [OptionalPackages::INSTALL_MODULAR, 3, 2, 'copy-files', 200, LaminasManagerContainer::class],
            'auryn-minimal'        => [OptionalPackages::INSTALL_MINIMAL, 4, 2, 'minimal-files', 404, AurynContainer::class],
            'auryn-flat'           => [OptionalPackages::INSTALL_FLAT,    4, 2, 'copy-files', 200, AurynContainer::class],
            'auryn-modular'        => [OptionalPackages::INSTALL_MODULAR, 4, 2, 'copy-files', 200, AurynContainer::class],
            'sf-di-minimal'        => [OptionalPackages::INSTALL_MINIMAL, 5, 2, 'minimal-files', 404, SfContainerBuilder::class],
            'sf-di-flat'           => [OptionalPackages::INSTALL_FLAT,    5, 2, 'copy-files', 200, SfContainerBuilder::class],
            'sf-di-modular'        => [OptionalPackages::INSTALL_MODULAR, 5, 2, 'copy-files', 200, SfContainerBuilder::class],
            'php-di-minimal'       => [OptionalPackages::INSTALL_MINIMAL, 6, 2, 'minimal-files', 404, PhpDIContainer::class],
            'php-di-flat'          => [OptionalPackages::INSTALL_FLAT,    6, 2, 'copy-files', 200, PhpDIContainer::class],
            'php-di-modular'       => [OptionalPackages::INSTALL_MODULAR, 6, 2, 'copy-files', 200, PhpDIContainer::class],
            'chubbyphp-c-minimal'  => [OptionalPackages::INSTALL_MINIMAL, 7, 2, 'minimal-files', 404, ChubbyphpContainer::class],
            'chubbyphp-c-flat'     => [OptionalPackages::INSTALL_FLAT,    7, 2, 'copy-files', 200, ChubbyphpContainer::class],
            'chubbyphp-c-modular'  => [OptionalPackages::INSTALL_MODULAR, 7, 2, 'copy-files', 200, ChubbyphpContainer::class],
        ];
        // @codingStandardsIgnoreEnd
    }
}
