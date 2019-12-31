<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use Mezzio;
use MezzioInstaller\OptionalPackages;

class TemplateRenderersTest extends InstallerTestCase
{
    protected $teardownFiles = [
        '/config/container.php',
        '/config/autoload/routes.global.php',
        '/config/autoload/templates.global.php',
        '/templates/error/404.phtml',
        '/templates/error/error.phtml',
        '/templates/layout/default.phtml',
        '/templates/app/home-page.phtml',
        '/templates/error/404.html.twig',
        '/templates/error/error.html.twig',
        '/templates/layout/default.html.twig',
        '/templates/app/home-page.html.twig',
    ];

    /**
     * @dataProvider templateRendererProvider
     */
    public function testTemplateRenderer(
        $containerOption,
        $routerOption,
        $templateRendererOption,
        $copyFilesKey,
        $expectedResponseStatusCode,
        $expectedTemplateRenderer
    ) {
        $io     = $this->prophesize('Composer\IO\IOInterface');
        $config = $this->getConfig();

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
        $this->assertTrue($container->has(Mezzio\Application::class));
        $this->assertTrue($container->has('Mezzio\FinalHandler'));

        // Test config
        $config = $container->get('config');
        $this->assertEquals(
            Mezzio\Container\TemplatedErrorHandlerFactory::class,
            $config['dependencies']['factories']['Mezzio\FinalHandler']
        );

        // Test template renderer
        $templateRenderer = $container->get(Mezzio\Template\TemplateRendererInterface::class);
        $this->assertInstanceOf(Mezzio\Template\TemplateRendererInterface::class, $templateRenderer);
        $this->assertInstanceOf($expectedTemplateRenderer, $templateRenderer);

        if ($copyFilesKey == 'copy-files') {
            // Test home page for full install only, otherwise you get invalid template name errors
            $response = $this->getAppResponse();
            $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
        }
    }

    public function templateRendererProvider()
    {
        // $containerOption, $routerOption, $templateRendererOption, $copyFilesKey, $expectedResponseStatusCode,
        // $expectedTemplateRenderer
        return [
            // Full tests first so all the template paths are created before the minimal tests start
            'plates-full'       => [3, 2, 1, 'copy-files', 200, Mezzio\Plates\PlatesRenderer::class],
            'twig-full'         => [3, 2, 2, 'copy-files', 200, Mezzio\Twig\TwigRenderer::class],
            'laminas-view-full'    => [3, 2, 3, 'copy-files', 200, Mezzio\LaminasView\LaminasViewRenderer::class],
            // Minimal tests must be after the full tests !!!
            'plates-minimal'    => [3, 2, 1, 'minimal-files', 404, Mezzio\Plates\PlatesRenderer::class],
            'twig-minimal'      => [3, 2, 2, 'minimal-files', 404, Mezzio\Twig\TwigRenderer::class],
            'laminas-view-minimal' => [3, 2, 3, 'minimal-files', 404, Mezzio\LaminasView\LaminasViewRenderer::class],
        ];
    }
}
