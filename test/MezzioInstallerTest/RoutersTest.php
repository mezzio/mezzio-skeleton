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

class RoutersTest extends InstallerTestCase
{
    protected $teardownFiles  = [
        '/config/container.php',
        '/config/autoload/routes.global.php',
    ];

    private   $expectedRoutes = [
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
