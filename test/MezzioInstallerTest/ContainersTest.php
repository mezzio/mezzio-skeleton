<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use Aura\Di\Container as AuraContainer;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceManager as LaminasManagerContainer;
use Mezzio;
use MezzioInstaller\OptionalPackages;
use Xtreamwayz\Pimple\Container as PimpleContainer;

class ContainersTest extends OptionalPackagesTestCase
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
     *
     * @dataProvider containerProvider
     *
     * @param string $installType
     * @param int $containerOption
     * @param int $routerOption
     * @param string $copyFilesKey
     * @param int $expectedResponseStatusCode
     * @param string $expectedContainer
     */
    public function testContainer(
        $installType,
        $containerOption,
        $routerOption,
        $copyFilesKey,
        $expectedResponseStatusCode,
        $expectedContainer
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
        $response = $this->getAppResponse('/', $setupRoutes);
        $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
    }

    public function containerProvider()
    {
        // @codingStandardsIgnoreStart
        // $installType, $containerOption, $routerOption, $copyFilesKey, $expectedResponseStatusCode, $expectedContainer
        return [
            'aura-minimal'    => [OptionalPackages::INSTALL_MINIMAL, 1, 2, 'minimal-files', 404, AuraContainer::class],
            'aura-flat'       => [OptionalPackages::INSTALL_FLAT,    1, 2, 'copy-files', 200, AuraContainer::class],
            'aura-modular'    => [OptionalPackages::INSTALL_MODULAR, 1, 2, 'copy-files', 200, AuraContainer::class],
            'pimple-minimal'  => [OptionalPackages::INSTALL_MINIMAL, 2, 2, 'minimal-files', 404, PimpleContainer::class],
            'pimple-flat'     => [OptionalPackages::INSTALL_FLAT,    2, 2, 'copy-files', 200, PimpleContainer::class],
            'pimple-modular'  => [OptionalPackages::INSTALL_MODULAR, 2, 2, 'copy-files', 200, PimpleContainer::class],
            'laminas-sm-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 'minimal-files', 404, LaminasManagerContainer::class],
            'laminas-sm-flat'    => [OptionalPackages::INSTALL_FLAT,    3, 2, 'copy-files', 200, LaminasManagerContainer::class],
            'laminas-sm-modular' => [OptionalPackages::INSTALL_MODULAR, 3, 2, 'copy-files', 200, LaminasManagerContainer::class],
        ];
        // @codingStandardsIgnoreEnd
    }
}
