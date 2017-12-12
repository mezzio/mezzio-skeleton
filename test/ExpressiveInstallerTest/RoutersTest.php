<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ExpressiveInstallerTest;

use App\Action\HomePageAction;
use App\Action\PingAction;
use ExpressiveInstaller\OptionalPackages;
use Zend\Expressive\Application;
use Zend\Expressive\Router;

class RoutersTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /**
     * @var array[]
     */
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
     * @dataProvider routerProvider
     */
    public function testRouter(
        string $installType,
        int $containerOption,
        int $routerOption,
        string $copyFilesKey,
        int $expectedResponseStatusCode,
        array $expectedRoutes,
        string $expectedRouter
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
        $this->assertTrue($container->has(Router\RouterInterface::class));

        // Test config
        $config = $container->get('config');
        $this->assertEquals(
            $expectedRouter,
            $config['dependencies']['invokables'][Router\RouterInterface::class]
        );

        // Test home page
        $setupRoutes = strpos($copyFilesKey, 'minimal') !== 0;
        $response = $this->getAppResponse('/', $setupRoutes);
        $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());

        /** @var Application $app */
        $app = $container->get(Application::class);
        $this->assertCount(count($expectedRoutes), $app->getRoutes());
        foreach ($app->getRoutes() as $route) {
            foreach ($expectedRoutes as $expectedRoute) {
                if ($expectedRoute['name'] === $route->getName()) {
                    $this->assertEquals($expectedRoute['path'], $route->getPath());
                    $this->assertEquals($expectedRoute['allowed_methods'], $route->getAllowedMethods());

                    continue 2;
                }
            }

            $this->fail(sprintf('Route with name "%s" has not been found', $route->getName()));
        }
    }

    public function routerProvider() : array
    {
        // @codingStandardsIgnoreStart
        // $containerOption, $routerOption, $copyFilesKey, $expectedResponseStatusCode, $expectedRoutes, $expectedRouter
        return [
            'aura-minimal'        => [OptionalPackages::INSTALL_MINIMAL, 3, 1, 'minimal-files', 404, [], Router\AuraRouter::class],
            'aura-flat'           => [OptionalPackages::INSTALL_FLAT, 3, 1, 'copy-files', 200, $this->expectedRoutes, Router\AuraRouter::class],
            'aura-modular'        => [OptionalPackages::INSTALL_MODULAR, 3, 1, 'copy-files', 200, $this->expectedRoutes, Router\AuraRouter::class],
            'fastroute-minimal'   => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 'minimal-files', 404, [], Router\FastRouteRouter::class],
            'fastroute-flat'      => [OptionalPackages::INSTALL_FLAT, 3, 2, 'copy-files', 200, $this->expectedRoutes, Router\FastRouteRouter::class],
            'fastroute-modular'   => [OptionalPackages::INSTALL_MODULAR, 3, 2, 'copy-files', 200, $this->expectedRoutes, Router\FastRouteRouter::class],
            'zend-router-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 3, 'minimal-files', 404, [], Router\ZendRouter::class],
            'zend-router-flat'    => [OptionalPackages::INSTALL_FLAT, 3, 3, 'copy-files', 200, $this->expectedRoutes, Router\ZendRouter::class],
            'zend-router-modular' => [OptionalPackages::INSTALL_MODULAR, 3, 3, 'copy-files', 200, $this->expectedRoutes, Router\ZendRouter::class],
        ];
        // @codingStandardsIgnoreEnd
    }
}
