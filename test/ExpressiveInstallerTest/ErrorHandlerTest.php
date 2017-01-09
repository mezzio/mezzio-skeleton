<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use Zend\Expressive\Container\WhoopsErrorResponseGeneratorFactory;
use Zend\Expressive\Middleware\ErrorResponseGenerator;

class ErrorHandlerTest extends InstallerTestCase
{
    protected $teardownFiles = [
        '/config/container.php',
        '/config/routes.php',
        '/config/autoload/errorhandler.local.php',
    ];

    public function testErrorHandlerIsNotInstalled()
    {
        $io     = $this->prophesize('Composer\IO\IOInterface');
        $config = $this->getConfig();

        // Install container
        $containerResult = OptionalPackages::processAnswer(
            $io->reveal(),
            $config['questions']['container'],
            3,
            'minimal-files'
        );
        $this->assertTrue($containerResult);

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has(ErrorResponseGenerator::class));
        $this->assertFalse($container->has('Zend\Expressive\Whoops'));
        $this->assertFalse($container->has('Zend\Expressive\WhoopsPageHandler'));
    }

    /**
     * @dataProvider errorHandlerProvider
     */
    public function testErrorHandler(
        $containerOption,
        $errorHandlerOption,
        $copyFilesKey,
        $expectedErrorHandler
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

        // Install error handler
        $containerResult = OptionalPackages::processAnswer(
            $io->reveal(),
            $config['questions']['error-handler'],
            $errorHandlerOption,
            $copyFilesKey
        );
        $this->assertTrue($containerResult);

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has(ErrorResponseGenerator::class));
        $this->assertTrue($container->has('Zend\Expressive\Whoops'));
        $this->assertTrue($container->has('Zend\Expressive\WhoopsPageHandler'));

        $config = $container->get('config');
        $this->assertEquals(
            $expectedErrorHandler,
            $config['dependencies']['factories'][ErrorResponseGenerator::class]
        );
    }

    public function errorHandlerProvider()
    {
        // $containerOption, $errorHandlerOption, $copyFilesKey, $expectedErrorHandler
        return [
            'whoops-minimal' => [3, 1, 'minimal-files', WhoopsErrorResponseGeneratorFactory::class],
            'whoops-full'    => [3, 1, 'copy-files', WhoopsErrorResponseGeneratorFactory::class],
        ];
    }
}
