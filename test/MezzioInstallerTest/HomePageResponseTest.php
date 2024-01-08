<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use Chubbyphp\Container\Container as ChubbyphpContainer;
use DI\Container as PhpDIContainer;
use Generator;
use Laminas\ServiceManager\ServiceManager as LaminasServiceManagerContainer;
use Mezzio\LaminasView\ConfigProvider as LaminasViewRendererConfigProvider;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Plates\ConfigProvider as PlatesRendererConfigProvider;
use Mezzio\Plates\PlatesRenderer;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\FastRouteRouter\ConfigProvider as FastRouteRouterConfigProvider;
use Mezzio\Router\LaminasRouter;
use Mezzio\Router\LaminasRouter\ConfigProvider as LaminasRouterConfigProvider;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\ConfigProvider as TwigRendererConfigProvider;
use Mezzio\Twig\TwigRenderer;
use MezzioInstaller\OptionalPackages;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;

use function chdir;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function json_decode;
use function preg_replace;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class HomePageResponseTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    private OptionalPackages $installer;

    /** @var array<class-string<RouterInterface>, class-string> */
    private static array $routerConfigProviders = [
        FastRouteRouter::class => FastRouteRouterConfigProvider::class,
        LaminasRouter::class   => LaminasRouterConfigProvider::class,
    ];

    /** @var array<class-string<TemplateRendererInterface>, class-string> */
    private static array $rendererConfigProviders = [
        PlatesRenderer::class      => PlatesRendererConfigProvider::class,
        TwigRenderer::class        => TwigRendererConfigProvider::class,
        LaminasViewRenderer::class => LaminasViewRendererConfigProvider::class,
    ];

    // $installType, $installType
    /** @var array<string, string> */
    private static array $installTypes = [
        OptionalPackages::INSTALL_FLAT    => OptionalPackages::INSTALL_FLAT,
        OptionalPackages::INSTALL_MODULAR => OptionalPackages::INSTALL_MODULAR,
    ];

    // $rendererOption, $rendererClass
    /** @var array<string, array<int|class-string<TemplateRendererInterface>>> */
    private static array $rendererTypes = [
        'plates'       => [1, PlatesRenderer::class],
        'twig'         => [2, TwigRenderer::class],
        'laminas-view' => [3, LaminasViewRenderer::class],
    ];

    // $routerOption, $routerClass
    /** @var array<string, array<int|class-string<RouterInterface>>> */
    private static array $routerTypes = [
        'fastroute'      => [1, FastRouteRouter::class],
        'laminas-router' => [2, LaminasRouter::class],
    ];

    /** @var array<class-string<RouterInterface>, array<string, string>> */
    private static array $expectedRouterAttributes = [
        FastRouteRouter::class => [
            'routerName' => 'FastRoute',
            'routerDocs' => 'https://github.com/nikic/FastRoute',
        ],
        LaminasRouter::class   => [
            'routerName' => 'Laminas Router',
            'routerDocs' => 'https://docs.laminas.dev/laminas-router/',
        ],
    ];

    // $containerOption, $containerClass
    /** @var array<string, array<int|class-string<ContainerInterface>>> */
    private static array $containerTypes = [
        'laminas-servicemanager' => [1, LaminasServiceManagerContainer::class],
        'sf-di'                  => [2, SfContainerBuilder::class],
        'php-di'                 => [3, PhpDIContainer::class],
        'chubbyphp-container'    => [4, ChubbyphpContainer::class],
    ];

    /** @var array<class-string<ContainerInterface>, array<string, string>> */
    private static array $expectedContainerAttributes = [
        LaminasServiceManagerContainer::class => [
            'containerName' => 'Laminas Servicemanager',
            'containerDocs' => 'https://docs.laminas.dev/laminas-servicemanager/',
        ],
        SfContainerBuilder::class             => [
            'containerName' => 'Symfony DI Container',
            'containerDocs' => 'https://symfony.com/doc/current/service_container.html',
        ],
        PhpDIContainer::class                 => [
            'containerName' => 'PHP-DI',
            'containerDocs' => 'https://php-di.org',
        ],
        ChubbyphpContainer::class             => [
            'containerName' => 'Chubbyphp Container',
            'containerDocs' => 'https://github.com/chubbyphp/chubbyphp-container',
        ],
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
     * @dataProvider installCasesProvider
     */
    public function testHomePageHtmlResponseContainsExpectedInfo(
        string $installType,
        int $containerOption,
        int $rendererOption,
        string $rendererClass,
        string $containerName,
        string $containerDocs
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
        $routerResult     = $this->installer->processAnswer(
            $config['questions']['router'],
            $routerOption = 2 // FastRoute, use assignment for clarity
        );
        self::assertTrue($routerResult);
        $this->injectRouterConfigProvider(FastRouteRouter::class);

        // Install template engine
        $templateEngineResult = $this->installer->processAnswer(
            $config['questions']['template-engine'],
            $rendererOption
        );
        self::assertTrue($templateEngineResult);
        $this->injectRendererConfigProvider($rendererClass);

        // Test home page response
        $response = $this->getAppResponse('/', true);
        self::assertEquals(200, $response->getStatusCode());

        // Test response content
        $html = $response->getBody()->__toString();

        self::assertStringContainsString(sprintf('Get started with %s', $containerName), $html);
        self::assertStringContainsString(sprintf('href="%s"', $containerDocs), $html);
    }

    /**
     * @psalm-return Generator<string, array{
     *     0: OptionalPackages::INSTALL_*,
     *     1: int,
     *     2: int,
     *     3: class-string<TemplateRendererInterface>,
     *     4: string,
     *     5: string
     * }>
     */
    public static function installCasesProvider(): Generator
    {
        // Execute a test case for each container, renderer and non minimal install type
        foreach (self::$containerTypes as $containerId => $containerType) {
            $containerOption = $containerType[0];
            $containerClass  = $containerType[1];

            $containerName = self::$expectedContainerAttributes[$containerClass]['containerName'];
            $containerDocs = self::$expectedContainerAttributes[$containerClass]['containerDocs'];

            foreach (self::$rendererTypes as $rendererId => $rendererType) {
                $rendererOption = $rendererType[0];
                $rendererClass  = $rendererType[1];

                // skip laminas-view / non laminas-servicemanager combinations
                if (3 === $rendererOption && 3 !== $containerOption) {
                    continue;
                }

                foreach (self::$installTypes as $installType) {
                    $name = implode('--', [$containerId, $rendererId, $installType]);
                    $args = [
                        $installType,
                        $containerOption,
                        $rendererOption,
                        $rendererClass,
                        $containerName,
                        $containerDocs,
                    ];

                    yield $name => $args;
                }
            }
        }
    }

    /**
     * @runInSeparateProcess
     * @dataProvider rendererlessInstallCasesProvider
     */
    public function testHomePageJsonResponseContainsExpectedInfo(
        string $installType,
        int $containerOption,
        string $containerName,
        string $containerDocs,
        int $routerOption,
        string $routerClass,
        string $routerName,
        string $routerDocs
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
        $this->injectRouterConfigProvider($routerClass);

        // Test home page response
        $response = $this->getAppResponse('/', true);
        self::assertEquals(200, $response->getStatusCode());

        // Test response content
        $json = $response->getBody()->__toString();
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertArrayHasKey('containerName', $data);
        self::assertArrayHasKey('containerDocs', $data);
        self::assertEquals($containerName, $data['containerName']);
        self::assertEquals($containerDocs, $data['containerDocs']);
        self::assertArrayHasKey('routerName', $data);
        self::assertArrayHasKey('routerDocs', $data);
        self::assertEquals($routerName, $data['routerName']);
        self::assertEquals($routerDocs, $data['routerDocs']);
    }

    /**
     * @psalm-return Generator<string, array{
     *     0: OptionalPackages::INSTALL_*,
     *     1: int,
     *     2: string,
     *     3: string,
     *     4: int,
     *     5: class-string<RouterInterface>,
     *     6: string,
     *     7: string
     * }>
     */
    public static function rendererlessInstallCasesProvider(): Generator
    {
        // Execute a test case for each install type and container, without any renderer
        foreach (self::$containerTypes as $containerId => $containerType) {
            $containerOption = $containerType[0];
            $containerClass  = $containerType[1];

            $containerName = self::$expectedContainerAttributes[$containerClass]['containerName'];
            $containerDocs = self::$expectedContainerAttributes[$containerClass]['containerDocs'];

            foreach (self::$routerTypes as $routerId => $routerType) {
                $routerOption = $routerType[0];
                $routerClass  = $routerType[1];
                $routerName   = self::$expectedRouterAttributes[$routerClass]['routerName'];
                $routerDocs   = self::$expectedRouterAttributes[$routerClass]['routerDocs'];

                foreach (self::$installTypes as $installType) {
                    $name = implode('--', [$containerId, $routerId, $installType]);
                    $args = [
                        $installType,
                        $containerOption,
                        $containerName,
                        $containerDocs,
                        $routerOption,
                        $routerClass,
                        $routerName,
                        $routerDocs,
                    ];

                    yield $name => $args;
                }
            }
        }
    }

    public function injectRouterConfigProvider(string $routerClass): void
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents   = file_get_contents($configFile);
        $contents   = preg_replace(
            '#(new ConfigAggregator\(\[)#s',
            '$1' . "\n    " . self::$routerConfigProviders[$routerClass] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }

    public function injectRendererConfigProvider(string $rendererClass): void
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents   = file_get_contents($configFile);
        $contents   = preg_replace(
            '#(new ConfigAggregator\(\[)#s',
            '$1' . "\n    " . self::$rendererConfigProviders[$rendererClass] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }
}
