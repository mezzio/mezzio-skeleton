<?php

namespace ExpressiveInstallerTest;

use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use ExpressiveInstaller\OptionalPackages;
use Interop\Container\ContainerInterface;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Application;

class InstallerTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IOInterface
     */
    private $io;

    private $composerDefinition;

    private $projectRoot;

    private $config;

    /**
     * @var ContainerInterface
     */
    private $container;

    private $response;

    public $testFiles = [];

    public function setup()
    {
        $this->response = null;

        $this->cleanup();

        $this->io = $this->prophesize('Composer\IO\IOInterface')->reveal();

        // Get composer.json
        $composerFile = Factory::getComposerFile();
        $json = new JsonFile($composerFile);
        OptionalPackages::$composerDefinition = $json->read();

        $this->projectRoot = realpath(dirname($composerFile));

        OptionalPackages::$config = require 'src/ExpressiveInstaller/config.php';
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
                OptionalPackages::copyFile($this->io, $this->projectRoot, $source, $target);
            }
        }
    }

    public function cleanup()
    {
        foreach ($this->testFiles as $file) {
            if (is_file($this->projectRoot.$file)) {
                unlink($this->projectRoot.$file);
            }
        }
    }

    public function getEmitter()
    {
        $self = $this;
        $emitter = $this->prophesize(EmitterInterface::class);
        $emitter
            ->emit(Argument::type(ResponseInterface::class))
            ->will(
                function ($args) use ($self) {
                    $response = array_shift($args);
                    $self->response = $response;

                    return null;
                }
            )
            ->shouldBeCalled()
        ;

        return $emitter->reveal();
    }

    public function getContainer()
    {
        if (!$this->container) {
            /** @var ContainerInterface $container */
            $this->container = require 'config/container.php';
        }

        return $this->container;
    }

    public function getHomePageResponse()
    {
        $container = $this->getContainer();

        /** @var Application $app */
        $app = $container->get('Zend\Expressive\Application');
        $request = new ServerRequest([], [], 'https://example.com/', 'GET');

        /** @var ResponseInterface $response */
        $response = $app($request, new Response());

        return $response;
    }
}
