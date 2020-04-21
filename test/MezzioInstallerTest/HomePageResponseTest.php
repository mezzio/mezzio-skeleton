<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioInstallerTest;

use Generator;
use Mezzio\Router\FastRouteRouter\ConfigProvider as FastRouteRouterConfigProvider;
use MezzioInstaller\OptionalPackages;
// Containers imports ordered by install-options sorting
use Aura\Di\Container as AuraDiContainer;
use Pimple\Psr11\Container as PimpleContainer;
use Laminas\ServiceManager\ServiceManager as LaminasServiceManagerContainer;
use Northwoods\Container\InjectorContainer as AurynContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use DI\Container as PhpDIContainer;
// Renderers imports ordered by install-options sorting
use Mezzio\Plates\ConfigProvider as PlatesRendererConfigProvider;
use Mezzio\Plates\PlatesRenderer;
use Mezzio\Twig\ConfigProvider as TwigRendererConfigProvider;
use Mezzio\Twig\TwigRenderer;
use Mezzio\LaminasView\ConfigProvider as LaminasViewRendererConfigProvider;
use Mezzio\LaminasView\LaminasViewRenderer;

class HomePageResponseTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /**
     * @var OptionalPackages
     */
    private $installer;

    private $rendererConfigProviders = [
        PlatesRenderer::class   => PlatesRendererConfigProvider::class,
        TwigRenderer::class     => TwigRendererConfigProvider::class,
        LaminasViewRenderer::class => LaminasViewRendererConfigProvider::class,
    ];

    // $intallType, $intallType
    private $intallTypes = [
        OptionalPackages::INSTALL_FLAT    => OptionalPackages::INSTALL_FLAT,
        OptionalPackages::INSTALL_MODULAR => OptionalPackages::INSTALL_MODULAR,
    ];

    // $rendererOption, $rendererClass
    private $rendererTypes = [
        'plates'       => [1, PlatesRenderer::class],
        'twig'         => [2, TwigRenderer::class],
        'laminas-view' => [3, LaminasViewRenderer::class],
    ];

    // $containerOption, $containerClass
    private $containerTypes = [
        'aura-di' => [1, AuraDiContainer::class],
        'pimple'  => [2, PimpleContainer::class],
        'laminas-servicemanager' => [3, LaminasServiceManagerContainer::class],
        'auryn'   => [4, AurynContainer::class],
        'sf-di'   => [5, SfContainerBuilder::class],
        'php-di'  => [6, PhpDIContainer::class],
    ];

    private $expectedContainerAttributes = [
        AuraDiContainer::class => [
            'containerName' => 'Aura.Di',
            'containerDocs' => 'http://auraphp.com/packages/4.x/Di/',
        ],
        PimpleContainer::class => [
            'containerName' => 'Pimple',
            'containerDocs' => 'https://pimple.symfony.com/',
        ],
        LaminasServiceManagerContainer::class => [
            'containerName' => 'Laminas Servicemanager',
            'containerDocs' => 'https://docs.laminas.dev/laminas-servicemanager/',
        ],
        AurynContainer::class => [
            'containerName' => 'Auryn',
            'containerDocs' => 'https://github.com/rdlowrey/Auryn',
        ],
        SfContainerBuilder::class => [
            'containerName' => 'Symfony DI Container',
            'containerDocs' => 'https://symfony.com/doc/current/service_container.html',
        ],
        PhpDIContainer::class => [
            'containerName' => 'PHP-DI',
            'containerDocs' => 'http://php-di.org',
        ],
    ];

    protected function setUp() : void
    {
        parent::setUp();
        $this->projectRoot = $this->copyProjectFilesToTempFilesystem();
        $this->installer   = $this->createOptionalPackages($this->projectRoot);
    }

    protected function tearDown() : void
    {
        parent::tearDown();
        chdir($this->packageRoot);
        $this->recursiveDelete($this->projectRoot);
        $this->tearDownAlternateAutoloader();
    }

    /**
     * @runInSeparateProcess
     *
     * @dataProvider installCasesProvider
     */
    public function testHomePageResponseContainsCorrectContainerInfo(
        string $installType,
        int $containerOption,
        string $containerClass,
        int $rendererOption,
        string $rendererClass,
        string $containerName,
        string $containerDocs
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
            $routerOption = 2 // FastRoute, use assignment for clarity
        );
        $this->assertTrue($routerResult);
        $this->injectRouterConfigProvider();

        // Install template engine
        $templateEngineResult = $this->installer->processAnswer(
            $config['questions']['template-engine'],
            $rendererOption
        );
        $this->assertTrue($templateEngineResult);
        $this->injectRendererConfigProvider($rendererClass);

        // Test home page response
        $response = $this->getAppResponse('/', true);
        $this->assertEquals(200, $response->getStatusCode());

        // Test response content
        $html = (string) $response->getBody()->getContents();

        $this->assertNotFalse(strpos($html, "Get started with {$containerName}"));
        $this->assertNotFalse(strpos($html, "href=\"{$containerDocs}\""));
    }

    public function installCasesProvider() : Generator
    {
        // Execute a test case for each container, renderer and non minimal install type
        foreach ($this->containerTypes as $containerID => $containerType) {
            $containerOption = $containerType[0];
            $containerClass  = $containerType[1];

            $containerName = $this->expectedContainerAttributes[$containerClass]['containerName'];
            $containerDocs = $this->expectedContainerAttributes[$containerClass]['containerDocs'];

            foreach ($this->rendererTypes as $rendererID => $rendererType) {
                $rendererOption = $rendererType[0];
                $rendererClass  = $rendererType[1];

                // skip laminas-view / non laminas-servicemanager combinations
                if (3 === $rendererOption && 3 !== $containerOption) {
                    continue;
                }

                foreach ($this->intallTypes as $intallType) {
                    $name = implode('--', [$containerID, $rendererID, $intallType]);
                    $args = [
                        $intallType,
                        $containerOption,
                        $containerClass,
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

    public function injectRouterConfigProvider()
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents = file_get_contents($configFile);
        $contents = preg_replace(
            '/(new ConfigAggregator\(\[)/s',
            '$1' . "\n    " . FastRouteRouterConfigProvider::class . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }

    public function injectRendererConfigProvider(string $rendererClass)
    {
        $configFile = $this->projectRoot . '/config/config.php';
        $contents = file_get_contents($configFile);
        $contents = preg_replace(
            '/(new ConfigAggregator\(\[)/s',
            '$1' . "\n    " . $this->rendererConfigProviders[$rendererClass] . "::class,\n",
            $contents
        );
        file_put_contents($configFile, $contents);
    }
}
