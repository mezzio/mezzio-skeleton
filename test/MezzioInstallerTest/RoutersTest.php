<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use App\Handler\HomePageHandler;
use App\Handler\PingHandler;
use Mezzio\Application;
use Mezzio\Router;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\FastRouteRouter\ConfigProvider;
use Mezzio\Router\LaminasRouter;
use Mezzio\Router\RouterInterface;
use MezzioInstaller\OptionalPackages;

use function chdir;
use function count;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
use function sprintf;
use function str_starts_with;

class RoutersTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /** @var array[] */
    private array $expectedRoutes = [
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

    private OptionalPackages $installer;

    /** @var string[] */
    private array $routerConfigProviders = [
        FastRouteRouter::class => ConfigProvider::class,
        LaminasRouter::class   => Router\LaminasRouter\ConfigProvider::class,
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
     * @param mixed[]|mixed[][] $expectedRoutes
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
    ): void {
        $this->prepareSandboxForInstallType($installType, $this->installer);

        // Install container
        $config          = $this->getInstallerConfig($this->installer);
        $containerResult = $this->installer->processAnswer(
            $config['questions']['container'],
            $containerOption
        );
        self::assertTrue($containerResult);

        // Install router
        $routerResult = $this->installer->processAnswer(
            $config['questions']['router'],
            $routerOption
        );
        self::assertTrue($routerResult);
        $this->enableRouter($expectedRouter);

        // Test container
        $container = $this->getContainer();
        self::assertTrue($container->has(RouterInterface::class));

        // Test config
        $config = $container->get('config');
        self::assertEquals(
            $expectedRouter,
            $config['dependencies'][$dependencyKey][RouterInterface::class]
        );

        // Test home page
        $setupRoutes = ! str_starts_with($copyFilesKey, 'minimal');
        $response    = $this->getAppResponse('/', $setupRoutes);
        self::assertEquals($expectedResponseStatusCode, $response->getStatusCode());

        /** @var Application $app */
        $app = $container->get(Application::class);
        self::assertCount(count($expectedRoutes), $app->getRoutes());
        foreach ($app->getRoutes() as $route) {
            foreach ($expectedRoutes as $expectedRoute) {
                if ($expectedRoute['name'] === $route->getName()) {
                    self::assertEquals($expectedRoute['path'], $route->getPath());
                    self::assertEquals($expectedRoute['allowed_methods'], $route->getAllowedMethods());

                    continue 2;
                }
            }

            self::fail(sprintf('Route with name "%s" has not been found', $route->getName()));
        }
    }

    public function routerProvider(): array
    {
        // @codingStandardsIgnoreStart
        // $installType, $containerOption, $routerOption, $copyFilesKey, $dependencyKey, $expectedResponseStatusCode, $expectedRoutes, $expectedRouter
        return [
            'fastroute-minimal'      => [OptionalPackages::INSTALL_MINIMAL, 2, 1, 'minimal-files', 'aliases', 404, [], FastRouteRouter::class],
            'fastroute-flat'         => [OptionalPackages::INSTALL_FLAT, 2, 1, 'copy-files', 'aliases', 200, $this->expectedRoutes, FastRouteRouter::class],
            'fastroute-modular'      => [OptionalPackages::INSTALL_MODULAR, 2, 1, 'copy-files', 'aliases', 200, $this->expectedRoutes, FastRouteRouter::class],
            'laminas-router-minimal' => [OptionalPackages::INSTALL_MINIMAL, 2, 2, 'minimal-files', 'aliases', 404, [], LaminasRouter::class],
            'laminas-router-flat'    => [OptionalPackages::INSTALL_FLAT, 2, 2, 'copy-files', 'aliases', 200, $this->expectedRoutes, LaminasRouter::class],
            'laminas-router-modular' => [OptionalPackages::INSTALL_MODULAR, 2, 2, 'copy-files', 'aliases', 200, $this->expectedRoutes, LaminasRouter::class],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function enableRouter(string $expectedRouter): void
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents   = file_get_contents($configFile);
        $contents   = preg_replace(
            '#(new ConfigAggregator\(\[)#',
            '$1' . "\n    " . $this->routerConfigProviders[$expectedRouter] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }
}
