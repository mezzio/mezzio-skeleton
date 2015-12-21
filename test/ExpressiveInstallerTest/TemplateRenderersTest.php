<?php

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use Zend\Expressive;

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
        // Install packages
        $this->installPackage(
            OptionalPackages::$config['questions']['container']['options'][$containerOption],
            $copyFilesKey
        );
        $this->installPackage(
            OptionalPackages::$config['questions']['router']['options'][$routerOption],
            $copyFilesKey
        );
        $this->installPackage(
            OptionalPackages::$config['questions']['template-engine']['options'][$templateRendererOption],
            $copyFilesKey
        );

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has(Expressive\Application::class));
        $this->assertTrue($container->has('Zend\Expressive\FinalHandler'));

        // Test config
        $config = $container->get('config');
        $this->assertEquals(
            Expressive\Container\TemplatedErrorHandlerFactory::class,
            $config['dependencies']['factories']['Zend\Expressive\FinalHandler']
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
            'plates-minimal'    => [3, 2, 1, 'minimal-files', 404, Expressive\Plates\PlatesRenderer::class],
            'plates-full'       => [3, 2, 1, 'copy-files', 200, Expressive\Plates\PlatesRenderer::class],
            'twig-minimal'      => [3, 2, 2, 'minimal-files', 404, Expressive\Twig\TwigRenderer::class],
            'twig-full'         => [3, 2, 2, 'copy-files', 200, Expressive\Twig\TwigRenderer::class],
            'zend-view-minimal' => [3, 2, 3, 'minimal-files', 404, Expressive\ZendView\ZendViewRenderer::class],
            'zend-view-full'    => [3, 2, 3, 'copy-files', 200, Expressive\ZendView\ZendViewRenderer::class],
        ];
    }
}
