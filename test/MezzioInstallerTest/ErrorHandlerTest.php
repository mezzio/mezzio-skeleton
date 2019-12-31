<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use Mezzio\Container\WhoopsErrorHandlerFactory;
use MezzioInstaller\OptionalPackages;

class ErrorHandlerTest extends InstallerTestCase
{
    protected $teardownFiles = [
        '/config/container.php',
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
        $this->assertFalse($container->has('Mezzio\FinalHandler'));
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
        $this->assertTrue($container->has('Mezzio\FinalHandler'));

        $config = $container->get('config');
        $this->assertEquals(
            $expectedErrorHandler,
            $config['dependencies']['factories']['Mezzio\FinalHandler']
        );
    }

    public function errorHandlerProvider()
    {
        // $containerOption, $errorHandlerOption, $copyFilesKey, $expectedErrorHandler
        return [
            'whoops-minimal' => [3, 1, 'minimal-files', WhoopsErrorHandlerFactory::class],
            'whoops-full'    => [3, 1, 'copy-files', WhoopsErrorHandlerFactory::class],
        ];
    }
}
