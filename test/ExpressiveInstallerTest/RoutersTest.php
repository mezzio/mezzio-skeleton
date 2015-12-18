<?php

namespace ExpressiveInstallerTest;

use App\Action\HomePageAction;
use App\Action\PingAction;
use ExpressiveInstaller\OptionalPackages;
use Zend\Expressive\Router;

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
            [3, 1, 'copy-files', 200, $this->expectedRoutes, Router\AuraRouter::class],
            [3, 2, 'copy-files', 200, $this->expectedRoutes, Router\FastRouteRouter::class],
            [3, 3, 'copy-files', 200, $this->expectedRoutes, Router\ZendRouter::class],

            [3, 1, 'minimal-files', 404, [], Router\AuraRouter::class],
            [3, 2, 'minimal-files', 404, [], Router\FastRouteRouter::class],
            [3, 3, 'minimal-files', 404, [], Router\ZendRouter::class],
        ];
    }
}
