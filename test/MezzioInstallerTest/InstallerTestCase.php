<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use Composer\Config;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Loader\RootPackageLoader;
use Composer\Repository\RepositoryManager;
use Interop\Container\ContainerInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Application;
use MezzioInstaller\OptionalPackages;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;

abstract class InstallerTestCase extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $teardownFiles = [];

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var ReflectionProperty
     */
    private $refConfig;

    /**
     * @var ReflectionProperty
     */
    private $refProjectRoot;

    /**
     * @var ReflectionProperty
     */
    private $refComposerDefinition;

    /**
     * @var ReflectionProperty
     */
    private $refComposerRequires;

    /**
     * @var ReflectionProperty
     */
    private $refComposerDevRequires;

    /**
     * @var ReflectionProperty
     */
    private $refStabilityFlags;

    protected function setup()
    {
        // Set config
        $this->refConfig = new ReflectionProperty(OptionalPackages::class, 'config');
        $this->refConfig->setAccessible(true);
        $this->refConfig->setValue(require 'src/MezzioInstaller/config.php');

        $this->io = $this->prophesize('Composer\IO\IOInterface');

        // Set composer.json
        $composerFile = Factory::getComposerFile();
        $json         = new JsonFile($composerFile);
        $localConfig  = $json->read();

        $this->refComposerDefinition = new ReflectionProperty(OptionalPackages::class, 'composerDefinition');
        $this->refComposerDefinition->setAccessible(true);
        $this->refComposerDefinition->setValue($localConfig);

        // Load parsed package data
        $manager        = $this->prophesize(RepositoryManager::class);
        $composerConfig = new Config;
        $composerConfig->merge(['repositories' => ['packagist' => false]]);
        $loader  = new RootPackageLoader($manager->reveal(), $composerConfig);
        $package = $loader->load($localConfig);

        // Set package data
        $this->refComposerRequires = new ReflectionProperty(OptionalPackages::class, 'composerRequires');
        $this->refComposerRequires->setAccessible(true);
        $this->refComposerRequires->setValue($package->getRequires());

        $this->refComposerDevRequires = new ReflectionProperty(OptionalPackages::class, 'composerDevRequires');
        $this->refComposerDevRequires->setAccessible(true);
        $this->refComposerDevRequires->setValue($package->getDevRequires());

        $this->refStabilityFlags = new ReflectionProperty(OptionalPackages::class, 'stabilityFlags');
        $this->refStabilityFlags->setAccessible(true);
        $this->refStabilityFlags->setValue($package->getStabilityFlags());

        // Set project root
        $this->refProjectRoot = new ReflectionProperty(OptionalPackages::class, 'projectRoot');
        $this->refProjectRoot->setAccessible(true);
        $this->refProjectRoot->setValue(realpath(dirname($composerFile)));

        // Cleanup old install files
        $this->cleanup();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->cleanup();
    }

    protected function cleanup()
    {
        foreach ($this->teardownFiles as $file) {
            if (is_file($this->getProjectRoot() . $file)) {
                unlink($this->getProjectRoot() . $file);
            }
        }
    }

    protected function getContainer()
    {
        if (!$this->container) {
            /** @var ContainerInterface $container */
            $this->container = require 'config/container.php';
        }

        return $this->container;
    }

    protected function getAppResponse($path = '/')
    {
        $container = $this->getContainer();

        /** @var Application $app */
        $app     = $container->get('Mezzio\Application');
        $request = new ServerRequest([], [], 'https://example.com' . $path, 'GET');

        /** @var ResponseInterface $response */
        $response = $app($request, new Response());

        return $response;
    }

    protected function getConfig()
    {
        return $this->refConfig->getValue();
    }

    protected function getProjectRoot()
    {
        return $this->refProjectRoot->getValue();
    }

    protected function getComposerDefinition()
    {
        return $this->refComposerDefinition->getValue();
    }

    protected function getComposerRequires()
    {
        return $this->refComposerRequires->getValue();
    }

    protected function getComposerDevRequires()
    {
        return $this->refComposerDevRequires->getValue();
    }

    protected function getStabilityFlags()
    {
        return $this->refStabilityFlags->getValue();
    }

    private function composerHasPackage($package)
    {
        if (array_key_exists($package, $this->getComposerRequires())) {
            return true;
        }

        if (array_key_exists($package, $this->getComposerDevRequires())) {
            return true;
        }

        return false;
    }

    private function composerDefinitionHasPackage($package)
    {
        $definition = $this->getComposerDefinition();

        if (array_key_exists($package, $definition['require'])) {
            return true;
        }

        if (array_key_exists($package, $definition['require-dev'])) {
            return true;
        }

        return false;
    }

    public function assertComposerHasPackages(array $packages)
    {
        $list = [];
        foreach ($packages as $package) {
            if (false === $this->composerHasPackage($package)
                || false === $this->composerDefinitionHasPackage($package)
            ) {
                $list[] = $package;
            }
        }

        $this->assertCount(0, $list, sprintf('Several packages were not found "%s"', implode(', ', $list)));
    }

    public function assertComposerNotHasPackages(array $packages)
    {
        $list = [];
        foreach ($packages as $package) {
            if (true === $this->composerHasPackage($package)
                || true === $this->composerDefinitionHasPackage($package)
            ) {
                $list[] = $package;
            }
        }

        $this->assertCount(0, $list, sprintf('Several packages were found "%s"', implode(', ', $list)));
    }
}
