<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use App\Action\HomePageAction;
use App\Action\PingAction;
use Mezzio\Router;
use MezzioInstaller\OptionalPackages;
use ReflectionProperty;

class RoutersTest extends InstallerTestCase
{
    protected $teardownFiles = [
        '/config/container.php',
        '/config/autoload/routes.global.php',
    ];

    private $expectedRoutes = [
        [
            'name'            => 'home',
            'path'            => '/',
            'middleware'      => HomePageAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name'            => 'api.ping',
            'path'            => '/api/ping',
            'middleware'      => PingAction::class,
            'allowed_methods' => ['GET'],
        ],
    ];

    /**
     * @dataProvider routerProvider
     */
    public function testRouter(
        $containerOption,
        $routerOption,
        $copyFilesKey,
        $expectedResponseStatusCode,
        $expectedRoutes,
        $expectedRouter
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
        $this->assertTrue($container->has(Router\RouterInterface::class));

        // Test config
        $config = $container->get('config');
        $this->assertEquals(
            $expectedRouter,
            $config['dependencies']['invokables'][Router\RouterInterface::class]
        );
        $this->assertEquals($expectedRoutes, $config['routes']);

        // Test home page
        $response = $this->getAppResponse();
        $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
    }

    public function routerProvider()
    {
        // $containerOption, $routerOption, $copyFilesKey, $expectedResponseStatusCode, $expectedRoutes, $expectedRouter
        return [
            'aura-minimal'        => [3, 1, 'minimal-files', 404, [], Router\AuraRouter::class],
            'aura-full'           => [3, 1, 'copy-files', 200, $this->expectedRoutes, Router\AuraRouter::class],
            'fastroute-minimal'   => [3, 2, 'minimal-files', 404, [], Router\FastRouteRouter::class],
            'fastroute-full'      => [3, 2, 'copy-files', 200, $this->expectedRoutes, Router\FastRouteRouter::class],
            'laminas-router-minimal' => [3, 3, 'minimal-files', 404, [], Router\LaminasRouter::class],
            'laminas-router-full'    => [3, 3, 'copy-files', 200, $this->expectedRoutes, Router\LaminasRouter::class],
        ];
    }
}
