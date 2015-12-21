<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use Aura\Di\Container as AuraContainer;
use ExpressiveInstaller\OptionalPackages;
use Interop\Container\ContainerInterface;
use Xtreamwayz\Pimple\Container as PimpleContainer;
use ReflectionProperty;
use Zend\Expressive;
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
    public function testContainer(
        $containerOption,
        $routerOption,
        $copyFilesKey,
        $expectedResponseStatusCode,
        $expectedContainer
    ) {
        $r = new ReflectionProperty(OptionalPackages::class, 'config');
        $r->setAccessible(true);
        $config = $r->getValue();

        // Install packages
        $this->installPackage(
            $config['questions']['container']['options'][$containerOption],
            $copyFilesKey
        );
        $this->installPackage(
            $config['questions']['router']['options'][$routerOption],
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
            'aura-minimal'    => [1, 2, 'minimal-files', 404, AuraContainer::class],
            'aura-full'       => [1, 2, 'copy-files', 200, AuraContainer::class],
            'pimple-minimal'  => [2, 2, 'minimal-files', 404, PimpleContainer::class],
            'pimple-full'     => [2, 2, 'copy-files', 200, PimpleContainer::class],
            'zend-sm-minimal' => [3, 2, 'minimal-files', 404, ZendServiceManagerContainer::class],
            'zend-sm-full'    => [3, 2, 'copy-files', 200, ZendServiceManagerContainer::class],
        ];
    }
}
