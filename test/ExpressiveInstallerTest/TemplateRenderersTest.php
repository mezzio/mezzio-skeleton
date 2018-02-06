<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use Generator;
use Zend\Expressive;
use Zend\Stratigility\Middleware;

class TemplateRenderersTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /**
     * @var OptionalPackages
     */
    private $installer;

    private $templateConfigProviders = [
        Expressive\Plates\PlatesRenderer::class => Expressive\Plates\ConfigProvider::class,
        Expressive\Twig\TwigRenderer::class => Expressive\Twig\ConfigProvider::class,
        Expressive\ZendView\ZendViewRenderer::class => Expressive\ZendView\ConfigProvider::class,
    ];

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
        $this->assertTrue($container->has(Expressive\Application::class));
        $this->assertTrue($container->has(Middleware\ErrorHandler::class));
        $this->assertTrue($container->has(Expressive\Template\TemplateRendererInterface::class));

        // Test config
        $config = $container->get('config');
        $this->assertEquals(
            Expressive\Container\ErrorHandlerFactory::class,
            $config['dependencies']['factories'][Middleware\ErrorHandler::class]
        );

        // Test template renderer
        $templateRenderer = $container->get(Expressive\Template\TemplateRendererInterface::class);
        $this->assertInstanceOf(Expressive\Template\TemplateRendererInterface::class, $templateRenderer);
        $this->assertInstanceOf($expectedTemplateRenderer, $templateRenderer);

        if ($installType !== OptionalPackages::INSTALL_MINIMAL) {
            // Test home page for non-minimal installs only, otherwise you get
            // invalid template name errors
            $response = $this->getAppResponse();
            $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
        }
    }

    public function templateRendererProvider() : Generator
    {
        // @codingStandardsIgnoreStart
        // Minimal framework installation test cases; no templates installed.
        // Must be run before those that install templates and test the output.
        // $installType, $containerOption, $routerOption, $templateRendererOption, $expectedResponseStatusCode, $expectedTemplateRenderer
        yield 'plates-minimal'    => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 1, 404, Expressive\Plates\PlatesRenderer::class];
        yield 'twig-minimal'      => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 2, 404, Expressive\Twig\TwigRenderer::class];
        yield 'zend-view-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 3, 404, Expressive\ZendView\ZendViewRenderer::class];
        // @codingStandardsIgnoreEnd

        // @codingStandardsIgnoreStart
        // Full framework installation test cases; installation options that install templates.
        $testCases = [
            // $containerOption, $routerOption, $templateRendererOption, $expectedResponseStatusCode, $expectedTemplateRenderer
            'plates-full'    => [3, 2, 1, 200, Expressive\Plates\PlatesRenderer::class],
            'twig-full'      => [3, 2, 2, 200, Expressive\Twig\TwigRenderer::class],
            'zend-view-full' => [3, 2, 3, 200, Expressive\ZendView\ZendViewRenderer::class],
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

    public function injectRouterConfigProvider()
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents = file_get_contents($configFile);
        $contents = preg_replace(
            '/(new ConfigAggregator\(\[)/s',
            '$1' . "\n    " . Expressive\Router\FastRouteRouter\ConfigProvider::class . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }

    public function injectConfigProvider(string $rendererClass)
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents = file_get_contents($configFile);
        $contents = preg_replace(
            '/(new ConfigAggregator\(\[)/s',
            '$1' . "\n    " . $this->templateConfigProviders[$rendererClass] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }
}
