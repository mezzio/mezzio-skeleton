<?php

namespace ExpressiveInstallerTest\Router;

use ExpressiveInstaller\OptionalPackages;
use ExpressiveInstallerTest\InstallerTestCase;

class AuraRouterTest extends InstallerTestCase
{

    public $testFiles = [
        '/config/container.php',
        '/config/autoload/routes.global.php',
    ];

    public function testFullInstall()
    {
        // Install packages
        $this->installPackage(OptionalPackages::$config['questions']['container']['options'][3], 'copy-files');
        $this->installPackage(OptionalPackages::$config['questions']['router']['options'][1], 'copy-files');

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has(\Zend\Expressive\Helper\UrlHelper::class));
        $this->assertTrue($container->has(\Zend\Expressive\Helper\ServerUrlHelper::class));
        $this->assertTrue($container->has(\Zend\Expressive\Application::class));
        $this->assertTrue($container->has(\Zend\Expressive\Router\RouterInterface::class));

        // Test config
        $config = $container->get('config');
        $this->assertEquals(
            \Zend\Expressive\Router\AuraRouter::class,
            $config['dependencies']['invokables'][\Zend\Expressive\Router\RouterInterface::class]
        );

        $routes = [
            [
                'name' => 'home',
                'path' => '/',
                'middleware' => \App\Action\HomePageAction::class,
                'allowed_methods' => ['GET'],
            ],
            [
                'name' => 'api.ping',
                'path' => '/api/ping',
                'middleware' => \App\Action\PingAction::class,
                'allowed_methods' => ['GET'],
            ],
        ];
        $this->assertEquals($routes, $config['routes']);

        // Test composer.json

        // Test home page
        $response = $this->getHomePageResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMinimalInstall()
    {
        // Install packages
        $this->installPackage(OptionalPackages::$config['questions']['container']['options'][3], 'minimal-files');
        $this->installPackage(OptionalPackages::$config['questions']['router']['options'][1], 'minimal-files');

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has(\Zend\Expressive\Router\RouterInterface::class));

        // Test config
        $config = $container->get('config');
        $this->assertEquals(
            \Zend\Expressive\Router\AuraRouter::class,
            $config['dependencies']['invokables'][\Zend\Expressive\Router\RouterInterface::class]
        );
        $this->assertEquals([], $config['routes']);

        // Test composer.json

        // Test home page
        $response = $this->getHomePageResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }
}
