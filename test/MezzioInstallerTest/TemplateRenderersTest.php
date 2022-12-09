<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use Generator;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio;
use Mezzio\Application;
use Mezzio\Container\ErrorHandlerFactory;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Plates\PlatesRenderer;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\TwigRenderer;
use MezzioInstaller\OptionalPackages;

use function array_unshift;
use function chdir;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
use function sprintf;

class TemplateRenderersTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    private OptionalPackages $installer;

    /** @var array<class-string, class-string> */
    private array $templateConfigProviders = [
        Mezzio\Plates\PlatesRenderer::class           => Mezzio\Plates\ConfigProvider::class,
        Mezzio\Twig\TwigRenderer::class               => Mezzio\Twig\ConfigProvider::class,
        Mezzio\LaminasView\LaminasViewRenderer::class => Mezzio\LaminasView\ConfigProvider::class,
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
     * @dataProvider templateRendererProvider
     * @param class-string $expectedTemplateRenderer
     */
    public function testTemplateRenderer(
        string $installType,
        int $containerOption,
        int $routerOption,
        int $templateRendererOption,
        int $expectedResponseStatusCode,
        string $expectedTemplateRenderer
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
        $this->injectRouterConfigProvider();

        // Install template engine
        $templateEngineResult = $this->installer->processAnswer(
            $config['questions']['template-engine'],
            $templateRendererOption
        );
        self::assertTrue($templateEngineResult);
        $this->injectConfigProvider($expectedTemplateRenderer);

        // Test container
        $container = $this->getContainer();
        self::assertTrue($container->has(Application::class));
        self::assertTrue($container->has(ErrorHandler::class));
        self::assertTrue($container->has(TemplateRendererInterface::class));

        // Test config
        $config = $container->get('config');
        self::assertEquals(
            ErrorHandlerFactory::class,
            $config['dependencies']['factories'][ErrorHandler::class]
        );

        // Test template renderer
        $templateRenderer = $container->get(TemplateRendererInterface::class);
        self::assertInstanceOf(TemplateRendererInterface::class, $templateRenderer);
        self::assertInstanceOf($expectedTemplateRenderer, $templateRenderer);

        if ($installType !== OptionalPackages::INSTALL_MINIMAL) {
            // Test home page for non-minimal installs only, otherwise you get
            // invalid template name errors
            $response = $this->getAppResponse();
            self::assertEquals($expectedResponseStatusCode, $response->getStatusCode());
        }
    }

    /**
     * @psalm-return Generator<string, array{
     *     0: OptionalPackages::INSTALL_*,
     *     1: int,
     *     2: int,
     *     3: int,
     *     4: int,
     *     5: class-string<Mezzio\Template\TemplateRendererInterface>
     * }>
     */
    public static function templateRendererProvider(): Generator
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        // Minimal framework installation test cases; no templates installed.
        // Must be run before those that install templates and test the output.
        // $installType, $containerOption, $routerOption, $templateRendererOption, $expectedResponseStatusCode, $expectedTemplateRenderer
        yield 'plates-minimal'       => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 1, 404, PlatesRenderer::class];
        yield 'twig-minimal'         => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 2, 404, TwigRenderer::class];
        yield 'laminas-view-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 3, 404, LaminasViewRenderer::class];

        // Full framework installation test cases; installation options that install templates.
        $testCases = [
            // $containerOption, $routerOption, $templateRendererOption, $expectedResponseStatusCode, $expectedTemplateRenderer
            'plates-full'       => [3, 2, 1, 200, PlatesRenderer::class],
            'twig-full'         => [3, 2, 2, 200, TwigRenderer::class],
            'laminas-view-full' => [3, 2, 3, 200, LaminasViewRenderer::class],
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong

        // Non-minimal installation types
        $types = [
            OptionalPackages::INSTALL_FLAT,
            OptionalPackages::INSTALL_MODULAR,
        ];

        // Execute a test case for each install type
        foreach ($types as $type) {
            foreach ($testCases as $testName => $arguments) {
                array_unshift($arguments, $type);
                $name = sprintf('%s-%s', $type, $testName);
                yield $name => $arguments;
            }
        }
    }

    public function injectRouterConfigProvider(): void
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents   = file_get_contents($configFile);
        $contents   = preg_replace(
            '#(new ConfigAggregator\(\[)#',
            '$1' . "\n    " . Mezzio\Router\FastRouteRouter\ConfigProvider::class . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }

    /** @param class-string $rendererClass */
    public function injectConfigProvider(string $rendererClass): void
    {
        self::assertArrayHasKey($rendererClass, $this->templateConfigProviders);
        $configFile = $this->projectRoot . '/config/config.php';
        $contents   = file_get_contents($configFile);
        $contents   = preg_replace(
            '#(new ConfigAggregator\(\[)#',
            '$1' . "\n    " . $this->templateConfigProviders[$rendererClass] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }
}
