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

class ContainersTest extends InstallerTestCase
{
    protected $teardownFiles = [
        '/config/container.php',
        '/config/autoload/routes.global.php',
    ];

    /**
     * @dataProvider containerProvider
     */
    public function testContainer(
        $containerOption,
        $routerOption,
        $copyFilesKey,
        $expectedResponseStatusCode,
        $expectedContainer
    ) {
        $io     = $this->prophesize('Composer\IO\IOInterface');
        $config = $this->getConfig();

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
        $this->assertTrue($container->has(Mezzio\Helper\UrlHelper::class));
        $this->assertTrue($container->has(Mezzio\Helper\ServerUrlHelper::class));
        $this->assertTrue($container->has(Mezzio\Application::class));
        $this->assertTrue($container->has(Mezzio\Router\RouterInterface::class));

        // Test home page
        $response = $this->getAppResponse();
        $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
    }

    public function containerProvider()
    {
        // $containerOption, $routerOption, $copyFilesKey, $expectedResponseStatusCode, $expectedContainer
        return [
            'aura-minimal'    => [1, 2, 'minimal-files', 404, AuraContainer::class],
            'aura-full'       => [1, 2, 'copy-files', 200, AuraContainer::class],
            'pimple-minimal'  => [2, 2, 'minimal-files', 404, PimpleContainer::class],
            'pimple-full'     => [2, 2, 'copy-files', 200, PimpleContainer::class],
            'laminas-sm-minimal' => [3, 2, 'minimal-files', 404, LaminasManagerContainer::class],
            'laminas-sm-full'    => [3, 2, 'copy-files', 200, LaminasManagerContainer::class],
        ];
    }
}
