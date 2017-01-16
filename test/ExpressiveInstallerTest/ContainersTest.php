<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use Aura\Di\Container as AuraContainer;
use ExpressiveInstaller\OptionalPackages;
use Interop\Container\ContainerInterface;
use Xtreamwayz\Pimple\Container as PimpleContainer;
use Zend\Expressive;
use Zend\ServiceManager\ServiceManager as ZendServiceManagerContainer;

class ContainersTest extends InstallerTestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        $this->setInstallType(OptionalPackages::INSTALL_FLAT);
    }

    /**
     * @dataProvider containerProvider
     * @runInSeparateProcess
     */
    public function testContainer(
        $installType,
        $containerOption,
        $routerOption,
        $copyFilesKey,
        $expectedResponseStatusCode,
        $expectedContainer
    ) {
        $projectRoot = $this->copyProjectFilesToVirtualFilesystem();
        $this->setProjectRoot($projectRoot);
        $this->setInstallType($installType);

        $io     = $this->prophesize('Composer\IO\IOInterface');
        $config = $this->getConfig();

        // Ensure we have an App\ConfigProvider defined
        OptionalPackages::setupDefaultApp($io->reveal(), $installType, $config['application']);

        // Install container
        $containerResult = OptionalPackages::processAnswer(
            $io->reveal(),
            $config['questions']['container'],
            $containerOption,
            $copyFilesKey
        );
        $this->assertTrue($containerResult);

        // Install router
        $routerResult = OptionalPackages::processAnswer(
            $io->reveal(),
            $config['questions']['router'],
            $routerOption,
            $copyFilesKey
        );
        $this->assertTrue($routerResult);

        // Test container
        $container = $this->getContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertInstanceOf($expectedContainer, $container);
        $this->assertTrue($container->has(Expressive\Helper\UrlHelper::class));
        $this->assertTrue($container->has(Expressive\Helper\ServerUrlHelper::class));
        $this->assertTrue($container->has(Expressive\Application::class));
        $this->assertTrue($container->has(Expressive\Router\RouterInterface::class));

        // Test home page
        $setupRoutes = (strpos($copyFilesKey, 'minimal') !== 0);
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
            'zend-sm-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 'minimal-files', 404, ZendServiceManagerContainer::class],
            'zend-sm-flat'    => [OptionalPackages::INSTALL_FLAT,    3, 2, 'copy-files', 200, ZendServiceManagerContainer::class],
            'zend-sm-modular' => [OptionalPackages::INSTALL_MODULAR, 3, 2, 'copy-files', 200, ZendServiceManagerContainer::class],
        ];
        // @codingStandardsIgnoreEnd
    }
}
