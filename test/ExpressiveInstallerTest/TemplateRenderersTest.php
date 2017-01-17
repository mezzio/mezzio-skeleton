<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use Zend\Expressive;
use Zend\Stratigility\Middleware;

class TemplateRenderersTest extends InstallerTestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        $this->setInstallType(OptionalPackages::INSTALL_FLAT);
    }

    /**
     * @dataProvider templateRendererProvider
     * @runInSeparateProcess
     */
    public function testTemplateRenderer(
        $installType,
        $containerOption,
        $routerOption,
        $templateRendererOption,
        $copyFilesKey,
        $expectedResponseStatusCode,
        $expectedTemplateRenderer
    ) {
        $projectRoot = $this->copyProjectFilesToVirtualFilesystem();
        $this->setProjectRoot($projectRoot);
        $this->setInstallType($installType);

        $io     = $this->prophesize('Composer\IO\IOInterface');
        $config = $this->getConfig();

        OptionalPackages::setupDefaultApp($io->reveal(), $installType, $config['application']);

        // Install container
        $containerResult = OptionalPackages::processAnswer(
            $io->reveal(),
            $config['questions']['container'],
            $containerOption,
            $copyFilesKey
        );
        $this->assertTrue($containerResult);

        // Install router
        $routerResult = OptionalPackages::processAnswer(
            $io->reveal(),
            $config['questions']['router'],
            $routerOption,
            $copyFilesKey
        );
        $this->assertTrue($routerResult);

        // Install template engine
        $templateEngineResult = OptionalPackages::processAnswer(
            $io->reveal(),
            $config['questions']['template-engine'],
            $templateRendererOption,
            $copyFilesKey
        );
        $this->assertTrue($templateEngineResult);

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

        if ($copyFilesKey == 'copy-files') {
            // Test home page for full install only, otherwise you get invalid template name errors
            $response = $this->getAppResponse();
            $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
        }
    }

    public function templateRendererProvider()
    {
        // @codingStandardsIgnoreStart
        // Minimal framework installation test cases; no templates installed.
        // Must be run before those that install templates and test the output.
        // $installType, $containerOption, $routerOption, $templateRendererOption, $copyFilesKey, $expectedResponseStatusCode, $expectedTemplateRenderer
        yield 'plates-minimal'    => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 1, 'minimal-files', 404, Expressive\Plates\PlatesRenderer::class];
        yield 'twig-minimal'      => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 2, 'minimal-files', 404, Expressive\Twig\TwigRenderer::class];
        yield 'zend-view-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 2, 3, 'minimal-files', 404, Expressive\ZendView\ZendViewRenderer::class];
        // @codingStandardsIgnoreEnd

        // @codingStandardsIgnoreStart
        // Full framework installation test cases; installation options that install templates.
        $testCases = [
            // $containerOption, $routerOption, $templateRendererOption, $copyFilesKey, $expectedResponseStatusCode, $expectedTemplateRenderer
            'plates-full'       => [3, 2, 1, 'copy-files', 200, Expressive\Plates\PlatesRenderer::class],
            'twig-full'         => [3, 2, 2, 'copy-files', 200, Expressive\Twig\TwigRenderer::class],
            'zend-view-full'    => [3, 2, 3, 'copy-files', 200, Expressive\ZendView\ZendViewRenderer::class],
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
}
