<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use ExpressiveInstaller\OptionalPackages;
use Interop\Container\ContainerInterface;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Application;

class InstallerTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IOInterface
     */
    private $io;

    private $projectRoot;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $response;

    protected $teardownFiles = [];

    public function setup()
    {
        $this->response = null;

        $this->cleanup();

        $this->io = $this->prophesize('Composer\IO\IOInterface');

        $composerDefinition = new ReflectionProperty(OptionalPackages::class, 'composerDefinition');
        $composerDefinition->setAccessible(true);

        // Get composer.json
        $composerFile = Factory::getComposerFile();
        $json = new JsonFile($composerFile);
        $composerDefinition->setValue($json->read());

        $this->projectRoot = realpath(dirname($composerFile));

        $config = new ReflectionProperty(OptionalPackages::class, 'config');
        $config->setAccessible(true);
        $config->setValue(require 'src/ExpressiveInstaller/config.php');
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->cleanup();
    }

    public function installPackage($config, $copyFilesKey)
    {
        /* TODO: First we need to set $composerDefinition, $composerRequires, $composerDevRequires and $stabilityFlags;
        // Add packages to install
        if (isset($config['packages'])) {
            foreach ($config['packages'] as $packageName) {
                OptionalPackages::addPackage($this->io, $packageName, $this->config['packages'][$packageName]);
            }
        }*/

        // Copy files
        if (isset($config[$copyFilesKey])) {
            foreach ($config[$copyFilesKey] as $source => $target) {
                OptionalPackages::copyFile($this->io->reveal(), $this->projectRoot, $source, $target);
            }
        }
    }

    public function cleanup()
    {
        foreach ($this->teardownFiles as $file) {
            if (is_file($this->projectRoot.$file)) {
                unlink($this->projectRoot.$file);
            }
        }
    }

    public function getContainer()
    {
        if (!$this->container) {
            /** @var ContainerInterface $container */
            $this->container = require 'config/container.php';
        }

        return $this->container;
    }

    public function getAppResponse($path = '/')
    {
        $container = $this->getContainer();

        /** @var Application $app */
        $app = $container->get('Zend\Expressive\Application');
        $request = new ServerRequest([], [], 'https://example.com'.$path, 'GET');

        /** @var ResponseInterface $response */
        $response = $app($request, new Response());

        return $response;
    }
}
