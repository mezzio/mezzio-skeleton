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
    protected function tearDown()
    {
        parent::tearDown();
        $this->setInstallType(OptionalPackages::INSTALL_FLAT);
    }

    /**
     * @runInSeparateProcess
     */
    public function testErrorHandlerIsNotInstalled()
    {
        $projectRoot = $this->copyProjectFilesToVirtualFilesystem();
        $this->setProjectRoot($projectRoot);
        $this->setInstallType(OptionalPackages::INSTALL_MINIMAL);

        $io     = $this->prophesize('Composer\IO\IOInterface');
        $config = $this->getConfig();

        OptionalPackages::setupDefaultApp($io->reveal(), OptionalPackages::INSTALL_MINIMAL, $config['application']);

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
     * @runInSeparateProcess
     */
    public function testErrorHandler(
        $installType,
        $containerOption,
        $errorHandlerOption,
        $copyFilesKey,
        $expectedErrorHandler
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
        // @codingStandardsIgnoreStart
        // $installType, $containerOption, $errorHandlerOption, $copyFilesKey, $expectedErrorHandler
        return [
            'whoops-minimal' => [OptionalPackages::INSTALL_MINIMAL, 3, 1, 'minimal-files', WhoopsErrorResponseGeneratorFactory::class],
            'whoops-full'    => [OptionalPackages::INSTALL_FLAT,    3, 1, 'copy-files', WhoopsErrorResponseGeneratorFactory::class],
            'whoops-modular' => [OptionalPackages::INSTALL_MODULAR, 3, 1, 'copy-files', WhoopsErrorResponseGeneratorFactory::class],
        ];
        // @codingStandardsIgnoreEnd
    }
}
