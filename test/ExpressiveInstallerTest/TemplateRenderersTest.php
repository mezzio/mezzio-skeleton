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
    protected $teardownFiles = [
        '/config/container.php',
        '/config/routes.php',
        '/config/autoload/routes.global.php',
        '/config/autoload/templates.global.php',
        '/src/App/templates/error/404.phtml',
        '/src/App/templates/error/error.phtml',
        '/src/App/templates/layout/default.phtml',
        '/src/App/templates/app/home-page.phtml',
        '/src/App/templates/error/404.html.twig',
        '/src/App/templates/error/error.html.twig',
        '/src/App/templates/layout/default.html.twig',
        '/src/App/templates/app/home-page.html.twig',
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
        $this->assertTrue($container->has(Expressive\Application::class));
        $this->assertTrue($container->has(Middleware\ErrorHandler::class));
        $this->assertTrue($container->has(Expressive\Middleware\ErrorResponseGenerator::class));

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
        // $containerOption, $routerOption, $templateRendererOption, $copyFilesKey, $expectedResponseStatusCode,
        // $expectedTemplateRenderer
        return [
            // Full tests first so all the template paths are created before the minimal tests start
            'plates-full'       => [3, 2, 1, 'copy-files', 200, Expressive\Plates\PlatesRenderer::class],
            'twig-full'         => [3, 2, 2, 'copy-files', 200, Expressive\Twig\TwigRenderer::class],
            'zend-view-full'    => [3, 2, 3, 'copy-files', 200, Expressive\ZendView\ZendViewRenderer::class],
            // Minimal tests must be after the full tests !!!
            'plates-minimal'    => [3, 2, 1, 'minimal-files', 404, Expressive\Plates\PlatesRenderer::class],
            'twig-minimal'      => [3, 2, 2, 'minimal-files', 404, Expressive\Twig\TwigRenderer::class],
            'zend-view-minimal' => [3, 2, 3, 'minimal-files', 404, Expressive\ZendView\ZendViewRenderer::class],
        ];
    }
}
