<?php

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use Interop\Container\ContainerInterface;
use Zend\Expressive;
use Aura\Di\Container as AuraContainer;
use Interop\Container\Pimple\PimpleInterop as PimpleInteropContainer;
use Zend\ServiceManager\ServiceManager as ZendServiceManagerContainer;

class ContainersTest extends InstallerTestCase
{

    protected $teardownFiles = [
        '/config/container.php',
        '/config/autoload/routes.global.php',
    ];

    /**
     * @dataProvider containerProvider
     */
    public function testFullInstall(
        $containerOption,
        $routerOption,
        $copyFilesKey,
        $expectedResponseStatusCode,
        $expectedContainer
    ) {
        // Install packages
        $this->installPackage(
            OptionalPackages::$config['questions']['container']['options'][$containerOption],
            $copyFilesKey
        );
        $this->installPackage(
            OptionalPackages::$config['questions']['router']['options'][$routerOption],
            $copyFilesKey
        );

        // Test container
        $container = $this->getContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertInstanceOf($expectedContainer, $container);
        $this->assertTrue($container->has(Expressive\Helper\UrlHelper::class));
        $this->assertTrue($container->has(Expressive\Helper\ServerUrlHelper::class));
        $this->assertTrue($container->has(Expressive\Application::class));
        $this->assertTrue($container->has(Expressive\Router\RouterInterface::class));

        // Test home page
        $response = $this->getAppResponse();
        $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
    }

    public function containerProvider()
    {
        // $containerOption, $routerOption, $copyFilesKey, $expectedResponseStatusCode, $expectedContainer
        return [
            [1, 2, 'copy-files', 200, AuraContainer::class],
            [2, 2, 'copy-files', 200, PimpleInteropContainer::class],
            [3, 2, 'copy-files', 200, ZendServiceManagerContainer::class],

            [1, 2, 'minimal-files', 404, AuraContainer::class],
            [2, 2, 'minimal-files', 404, PimpleInteropContainer::class],
            [3, 2, 'minimal-files', 404, ZendServiceManagerContainer::class],
        ];
    }
}
