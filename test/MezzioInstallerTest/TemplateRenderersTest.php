<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioInstallerTest;

use Generator;
use Laminas\Stratigility\Middleware;
use Mezzio;
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

    /** @var OptionalPackages */
    private $installer;

    /** @var string[] */
    private $templateConfigProviders = [
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
     */
    public function testTemplateRenderer(
        string $installType,
        int $containerOption,
        int $routerOption,
        int $templateRendererOption,
        int $expectedResponseStatusCode,
        string $expectedTemplateRenderer
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
        $this->injectRouterConfigProvider();

        // Install template engine
        $templateEngineResult = $this->installer->processAnswer(
            $config['questions']['template-engine'],
            $templateRendererOption
        );
        $this->assertTrue($templateEngineResult);
        $this->injectConfigProvider($expectedTemplateRenderer);

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has(Mezzio\Application::class));
        $this->assertTrue($container->has(Middleware\ErrorHandler::class));
        $this->assertTrue($container->has(Mezzio\Template\TemplateRendererInterface::class));

        // Test config
        $config = $container->get('config');
        $this->assertEquals(
            Mezzio\Container\ErrorHandlerFactory::class,
            $config['dependencies']['factories'][Middleware\ErrorHandler::class]
        );

        // Test template renderer
        $templateRenderer = $container->get(Mezzio\Template\TemplateRendererInterface::class);
        $this->assertInstanceOf(Mezzio\Template\TemplateRendererInterface::class, $templateRenderer);
        $this->assertInstanceOf($expectedTemplateRenderer, $templateRenderer);

        if ($installType !== OptionalPackages::INSTALL_MINIMAL) {
            // Test home page for non-minimal installs only, otherwise you get
            // invalid template name errors
            $response = $this->getAppResponse();
            $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
        }
    }

    public function templateRendererProvider(): Generator
    {
        // @codingStandardsIgnoreStart
        // Minimal framework installation test cases; no templates installed.
        // Must be run before those that install templates and test the output.
        // $installType, $containerOption, $routerOption, $templateRendererOption, $expectedResponseStatusCode, $expectedTemplateRenderer
        yield 'plates-minimal'    => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 1, 404, Mezzio\Plates\PlatesRenderer::class];
        yield 'twig-minimal'      => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 2, 404, Mezzio\Twig\TwigRenderer::class];
        yield 'laminas-view-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 3, 404, Mezzio\LaminasView\LaminasViewRenderer::class];
        // @codingStandardsIgnoreEnd

        // @codingStandardsIgnoreStart
        // Full framework installation test cases; installation options that install templates.
        $testCases = [
            // $containerOption, $routerOption, $templateRendererOption, $expectedResponseStatusCode, $expectedTemplateRenderer
            'plates-full'    => [3, 2, 1, 200, Mezzio\Plates\PlatesRenderer::class],
            'twig-full'      => [3, 2, 2, 200, Mezzio\Twig\TwigRenderer::class],
            'laminas-view-full' => [3, 2, 3, 200, Mezzio\LaminasView\LaminasViewRenderer::class],
        ];
        // @codingStandardsIgnoreEnd

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
        $contents = file_get_contents($configFile);
        $contents = preg_replace(
            '/(new ConfigAggregator\(\[)/',
            '$1' . "\n    " . Mezzio\Router\FastRouteRouter\ConfigProvider::class . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }

    public function injectConfigProvider(string $rendererClass): void
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents = file_get_contents($configFile);
        $contents = preg_replace(
            '/(new ConfigAggregator\(\[)/',
            '$1' . "\n    " . $this->templateConfigProviders[$rendererClass] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }
}
