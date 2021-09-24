<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use Aura\Di\Container as AuraDiContainer;
use Chubbyphp\Container\Container as ChubbyphpContainer;
use DI\Container as PhpDIContainer;
use Generator;
use Laminas\ServiceManager\ServiceManager as LaminasServiceManagerContainer;
use Mezzio\LaminasView\ConfigProvider as LaminasViewRendererConfigProvider;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Plates\ConfigProvider as PlatesRendererConfigProvider;
use Mezzio\Plates\PlatesRenderer;
use Mezzio\Router\AuraRouter;
use Mezzio\Router\AuraRouter\ConfigProvider as AuraRouterConfigProvider;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\FastRouteRouter\ConfigProvider as FastRouteRouterConfigProvider;
use Mezzio\Router\LaminasRouter;
use Mezzio\Router\LaminasRouter\ConfigProvider as LaminasRouterConfigProvider;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\ConfigProvider as TwigRendererConfigProvider;
use Mezzio\Twig\TwigRenderer;
use MezzioInstaller\OptionalPackages;
use Northwoods\Container\InjectorContainer as AurynContainer;
use Pimple\Psr11\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;

use function chdir;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function json_decode;
use function preg_replace;

class HomePageResponseTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /** @var OptionalPackages */
    private $installer;

    /** @var array<class-string<RouterInterface>, class-string> */
    private $routerConfigProviders = [
        AuraRouter::class      => AuraRouterConfigProvider::class,
        FastRouteRouter::class => FastRouteRouterConfigProvider::class,
        LaminasRouter::class   => LaminasRouterConfigProvider::class,
    ];

    /** @var array<class-string<TemplateRendererInterface>, class-string> */
    private $rendererConfigProviders = [
        PlatesRenderer::class      => PlatesRendererConfigProvider::class,
        TwigRenderer::class        => TwigRendererConfigProvider::class,
        LaminasViewRenderer::class => LaminasViewRendererConfigProvider::class,
    ];

    // $installType, $installType
    /** @var array<string, string> */
    private $installTypes = [
        OptionalPackages::INSTALL_FLAT    => OptionalPackages::INSTALL_FLAT,
        OptionalPackages::INSTALL_MODULAR => OptionalPackages::INSTALL_MODULAR,
    ];

    // $rendererOption, $rendererClass
    /** @var array<string, array<int|class-string<TemplateRendererInterface>>> */
    private $rendererTypes = [
        'plates'       => [1, PlatesRenderer::class],
        'twig'         => [2, TwigRenderer::class],
        'laminas-view' => [3, LaminasViewRenderer::class],
    ];

    // $routerOption, $routerClass
    /** @var array<string, array<int|class-string<RouterInterface>>> */
    private $routerTypes = [
        'aura-router'    => [1, AuraRouter::class],
        'fastroute'      => [2, FastRouteRouter::class],
        'laminas-router' => [3, LaminasRouter::class],
    ];

    /** @var array<class-string<RouterInterface>, array<string, string>> */
    private $expectedRouterAttributes = [
        AuraRouter::class      => [
            'routerName' => 'Aura.Router',
            'routerDocs' => 'http://auraphp.com/packages/3.x/Router/',
        ],
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
    private $containerTypes = [
        'aura-di'                => [1, AuraDiContainer::class],
        'pimple'                 => [2, PimpleContainer::class],
        'laminas-servicemanager' => [3, LaminasServiceManagerContainer::class],
        'auryn'                  => [4, AurynContainer::class],
        'sf-di'                  => [5, SfContainerBuilder::class],
        'php-di'                 => [6, PhpDIContainer::class],
        'chubbyphp-container'    => [7, ChubbyphpContainer::class],
    ];

    /** @var array<class-string<ContainerInterface>, array<string, string>> */
    private $expectedContainerAttributes = [
        AuraDiContainer::class                => [
            'containerName' => 'Aura.Di',
            'containerDocs' => 'http://auraphp.com/packages/4.x/Di/',
        ],
        PimpleContainer::class                => [
            'containerName' => 'Pimple',
            'containerDocs' => 'https://pimple.symfony.com/',
        ],
        LaminasServiceManagerContainer::class => [
            'containerName' => 'Laminas Servicemanager',
            'containerDocs' => 'https://docs.laminas.dev/laminas-servicemanager/',
        ],
        AurynContainer::class                 => [
            'containerName' => 'Auryn',
            'containerDocs' => 'https://github.com/rdlowrey/Auryn',
        ],
        SfContainerBuilder::class             => [
            'containerName' => 'Symfony DI Container',
            'containerDocs' => 'https://symfony.com/doc/current/service_container.html',
        ],
        PhpDIContainer::class                 => [
            'containerName' => 'PHP-DI',
            'containerDocs' => 'http://php-di.org',
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
    ) {
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
        $html = (string) $response->getBody()->getContents();

        self::assertStringContainsString("Get started with {$containerName}", $html);
        self::assertStringContainsString("href=\"{$containerDocs}\"", $html);
    }

    public function installCasesProvider(): Generator
    {
        // Execute a test case for each container, renderer and non minimal install type
        foreach ($this->containerTypes as $containerId => $containerType) {
            $containerOption = $containerType[0];
            $containerClass  = $containerType[1];

            $containerName = $this->expectedContainerAttributes[$containerClass]['containerName'];
            $containerDocs = $this->expectedContainerAttributes[$containerClass]['containerDocs'];

            foreach ($this->rendererTypes as $rendererId => $rendererType) {
                $rendererOption = $rendererType[0];
                $rendererClass  = $rendererType[1];

                // skip laminas-view / non laminas-servicemanager combinations
                if (3 === $rendererOption && 3 !== $containerOption) {
                    continue;
                }

                foreach ($this->installTypes as $installType) {
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
    ) {
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
        $json = (string) $response->getBody()->getContents();
        $data = json_decode($json, true);

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

    public function rendererlessInstallCasesProvider(): Generator
    {
        // Execute a test case for each install type and container, without any renderer
        foreach ($this->containerTypes as $containerId => $containerType) {
            // auryn psr-wrapper : issue with invokable services
            if ($containerId === 'auryn') {
                continue;
            }

            $containerOption = $containerType[0];
            $containerClass  = $containerType[1];

            $containerName = $this->expectedContainerAttributes[$containerClass]['containerName'];
            $containerDocs = $this->expectedContainerAttributes[$containerClass]['containerDocs'];

            foreach ($this->routerTypes as $routerId => $routerType) {
                $routerOption = $routerType[0];
                $routerClass  = $routerType[1];
                $routerName   = $this->expectedRouterAttributes[$routerClass]['routerName'];
                $routerDocs   = $this->expectedRouterAttributes[$routerClass]['routerDocs'];

                foreach ($this->installTypes as $installType) {
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

    public function injectRouterConfigProvider(string $routerClass)
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents   = file_get_contents($configFile);
        $contents   = preg_replace(
            '/(new ConfigAggregator\(\[)/s',
            '$1' . "\n    " . $this->routerConfigProviders[$routerClass] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }

    public function injectRendererConfigProvider(string $rendererClass)
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents   = file_get_contents($configFile);
        $contents   = preg_replace(
            '/(new ConfigAggregator\(\[)/s',
            '$1' . "\n    " . $this->rendererConfigProviders[$rendererClass] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }
}
