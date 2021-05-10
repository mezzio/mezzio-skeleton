<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use App\Handler\HomePageHandler;
use App\Handler\PingHandler;
use Mezzio\Application;
use Mezzio\Router;
use MezzioInstaller\OptionalPackages;

use function chdir;
use function count;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
use function sprintf;
use function strpos;

class RoutersTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /** @var array[] */
    private $expectedRoutes = [
        [
            'name'            => 'home',
            'path'            => '/',
            'middleware'      => HomePageHandler::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name'            => 'api.ping',
            'path'            => '/api/ping',
            'middleware'      => PingHandler::class,
            'allowed_methods' => ['GET'],
        ],
    ];

    /** @var OptionalPackages */
    private $installer;

    /** @var string[] */
    private $routerConfigProviders = [
        Router\AuraRouter::class      => Router\AuraRouter\ConfigProvider::class,
        Router\FastRouteRouter::class => Router\FastRouteRouter\ConfigProvider::class,
        Router\LaminasRouter::class   => Router\LaminasRouter\ConfigProvider::class,
    ];

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
     * @dataProvider routerProvider
     */
    public function testRouter(
        string $installType,
        int $containerOption,
        int $routerOption,
        string $copyFilesKey,
        string $dependencyKey,
        int $expectedResponseStatusCode,
        array $expectedRoutes,
        string $expectedRouter
    ) {
        $this->prepareSandboxForInstallType($installType, $this->installer);

        // Install container
        $config          = $this->getInstallerConfig($this->installer);
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
        $this->enableRouter($expectedRouter);

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has(Router\RouterInterface::class));

        // Test config
        $config = $container->get('config');
        $this->assertEquals(
            $expectedRouter,
            $config['dependencies'][$dependencyKey][Router\RouterInterface::class]
        );

        // Test home page
        $setupRoutes = strpos($copyFilesKey, 'minimal') !== 0;
        $response    = $this->getAppResponse('/', $setupRoutes);
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

    public function routerProvider(): array
    {
        // @codingStandardsIgnoreStart
        // $containerOption, $routerOption, $copyFilesKey, $dependencyKey, $expectedResponseStatusCode, $expectedRoutes, $expectedRouter
        return [
            'aura-minimal'           => [OptionalPackages::INSTALL_MINIMAL, 3, 1, 'minimal-files', 'aliases', 404, [], Router\AuraRouter::class],
            'aura-flat'              => [OptionalPackages::INSTALL_FLAT, 3, 1, 'copy-files', 'aliases', 200, $this->expectedRoutes, Router\AuraRouter::class],
            'aura-modular'           => [OptionalPackages::INSTALL_MODULAR, 3, 1, 'copy-files', 'aliases', 200, $this->expectedRoutes, Router\AuraRouter::class],
            'fastroute-minimal'      => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 'minimal-files', 'aliases', 404, [], Router\FastRouteRouter::class],
            'fastroute-flat'         => [OptionalPackages::INSTALL_FLAT, 3, 2, 'copy-files', 'aliases', 200, $this->expectedRoutes, Router\FastRouteRouter::class],
            'fastroute-modular'      => [OptionalPackages::INSTALL_MODULAR, 3, 2, 'copy-files', 'aliases', 200, $this->expectedRoutes, Router\FastRouteRouter::class],
            'laminas-router-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 3, 'minimal-files', 'aliases', 404, [], Router\LaminasRouter::class],
            'laminas-router-flat'    => [OptionalPackages::INSTALL_FLAT, 3, 3, 'copy-files', 'aliases', 200, $this->expectedRoutes, Router\LaminasRouter::class],
            'laminas-router-modular' => [OptionalPackages::INSTALL_MODULAR, 3, 3, 'copy-files', 'aliases', 200, $this->expectedRoutes, Router\LaminasRouter::class],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function enableRouter(string $expectedRouter): void
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents   = file_get_contents($configFile);
        $contents   = preg_replace(
            '/(new ConfigAggregator\(\[)/',
            '$1' . "\n    " . $this->routerConfigProviders[$expectedRouter] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }
}
