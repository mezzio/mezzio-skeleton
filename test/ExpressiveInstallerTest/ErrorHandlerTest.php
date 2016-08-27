<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use ReflectionProperty;
use Zend\Expressive\Container\WhoopsErrorHandlerFactory;

class ErrorHandlerTest extends InstallerTestCase
{
    protected $teardownFiles = [
        '/config/container.php',
        '/config/autoload/errorhandler.local.php',
    ];

    public function testErrorHandlerIsNotInstalled()
    {
        $r = new ReflectionProperty(OptionalPackages::class, 'config');
        $r->setAccessible(true);
        $config = $r->getValue();

        // Install packages
        $this->installPackage($config['questions']['container']['options'][3], 'minimal-files');

        // Test container
        $container = $this->getContainer();
        $this->assertFalse($container->has('Zend\Expressive\FinalHandler'));
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
        $r = new ReflectionProperty(OptionalPackages::class, 'config');
        $r->setAccessible(true);
        $config = $r->getValue();

        // Install packages
        $this->installPackage(
            $config['questions']['container']['options'][$containerOption],
            $copyFilesKey
        );
        $this->installPackage(
            $config['questions']['error-handler']['options'][$errorHandlerOption],
            $copyFilesKey
        );

        // Test container
        $container = $this->getContainer();
        $this->assertTrue($container->has('Zend\Expressive\FinalHandler'));

        $config = $container->get('config');
        $this->assertEquals(
            $expectedErrorHandler,
            $config['dependencies']['factories']['Zend\Expressive\FinalHandler']
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
