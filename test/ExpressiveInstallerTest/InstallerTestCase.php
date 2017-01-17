<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use App\Action\HomePageAction;
use App\Action\PingAction;
use Composer\Config;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Loader\RootPackageLoader;
use Composer\Repository\RepositoryManager;
use DirectoryIterator;
use ExpressiveInstaller\OptionalPackages;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionProperty;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Middleware\NotFoundHandler;
use Zend\Stratigility\Middleware\ErrorHandler;

abstract class InstallerTestCase extends TestCase
{
    /**
     * @var null|callable Additional autoloader to prepend to stack.
     *     Used when flat install is requested.
     */
    protected $autoloader;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $origProjectRoot;

    /**
     * @var string Filesystem location of test project.
     */
    protected $projectRoot;

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
        $this->origProjectRoot = realpath(__DIR__ . '/../../');

        // Set config
        $this->refConfig = new ReflectionProperty(OptionalPackages::class, 'config');
        $this->refConfig->setAccessible(true);
        $this->refConfig->setValue(require 'src/ExpressiveInstaller/config.php');

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

        if ($this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            unset($this->autoloader);
        }
    }

    /**
     * Adds an alternate autoloader to the stack for the App namespace.
     *
     * Required, as the tests will load classes from that namespace, but the
     * class files will exist in temporary directories.
     *
     * Any test that uses this (and it's implicit when using setInstallType())
     * MUST run in a separate process.
     *
     * tearDown() unregisters the autoloader.
     *
     * @param string $appPath The path to the App namespace source code,
     *     relative to the project root.
     * @param bool $stripNamespace Whether or not to strip the initial
     *     namespace when determining the path (ala PSR-4).
     */
    protected function setUpAlternateAutoloader($appPath, $stripNamespace = false)
    {
        $this->autoloader = function ($class) use ($appPath, $stripNamespace) {
            if (0 !== strpos($class, 'App\\')) {
                return false;
            }

            $class = $stripNamespace
                ? str_replace('App\\', '', $class)
                : $class;

            $path = $this->projectRoot
                . $appPath
                . str_replace('\\', '/', $class)
                . '.php';

            if (! file_exists($path)) {
                return false;
            }

            include $path;
        };

        spl_autoload_register($this->autoloader, true, true);
    }

    protected function cleanup()
    {
        foreach ($this->teardownFiles as $file) {
            if (is_file($this->getProjectRoot() . $file)) {
                unlink($this->getProjectRoot() . $file);
            }
        }

        if ($this->projectRoot) {
            chdir($this->origProjectRoot);
            $this->recursiveDelete($this->projectRoot);
            $this->setProjectRoot(null);
            $this->projectRoot = null;
        }
    }

    /**
     * @param null|string $root
     */
    protected function setProjectRoot($root)
    {
        $r = new ReflectionProperty(OptionalPackages::class, 'projectRoot');
        $r->setAccessible(true);
        $r->setValue($root);
    }

    /**
     * Copies the project files into a temporary filesystem.
     *
     * Sets the path to the new temporary filesystem in the $projectRoot
     * property, changes the working directory to that new location, and
     * returns the location.
     *
     * cleanup() recursively removes the created directory.
     */
    protected function copyProjectFilesToVirtualFilesystem()
    {
        $this->projectRoot = sys_get_temp_dir() . '/' . uniqid('exp');

        mkdir($this->projectRoot . '/data', 0777, true);
        foreach (['config', 'public', 'src', 'templates', 'test'] as $path) {
            $this->recursiveCopy(
                $this->origProjectRoot . '/' . $path,
                $this->projectRoot . '/' . $path
            );
        }

        chdir($this->projectRoot);
        return $this->projectRoot;
    }

    /**
     * Recursively copy the files from one tree to another.
     *
     * @param string $source Source tree to copy.
     * @param string $target Target tree to copy into.
     */
    protected function recursiveCopy($source, $target)
    {
        if (! is_dir($target)) {
            mkdir($target, 0777, true);
        }

        if (! is_dir($source)) {
            return;
        }

        $dir = new DirectoryIterator($source);
        foreach ($dir as $fileInfo) {
            if ($fileInfo->isFile()) {
                $realPath = $fileInfo->getRealPath();
                $path = ltrim(str_replace($source, '', $realPath), '/\\');
                copy($realPath, sprintf('%s/%s', $target, $path));
                continue;
            }

            if ($fileInfo->isDir()
            ) {
                $path = $fileInfo->getFilename();
                if (in_array($path, ['.', '..'], true)) {
                    continue;
                }

                mkdir($target . '/' . $path, 0777, true);

                $this->recursiveCopy(
                    $source . '/' . $path,
                    $target . '/' . $path
                );
                continue;
            }
        }
    }

    /**
     * Recursively remove a filesystem tree.
     *
     * @param string $target Tree to remove.
     */
    protected function recursiveDelete($target)
    {
        if (! is_dir($target)) {
            return;
        }

        foreach (scandir($target) as $node) {
            if (in_array($node, ['.', '..'], true)) {
                continue;
            }

            $path = sprintf('%s/%s', $target, $node);

            if (is_dir($path)) {
                $this->recursiveDelete($path);
                continue;
            }

            unlink($path);
        }

        rmdir($target);
    }

    /**
     * Set the installation type (minimal, flat, modular).
     *
     * When set, determines how files are copied into the project and the tree
     * organized.
     *
     * For FLAT and MODULAR installations, this also sets up autoloading for the
     * App namespace; as such, if you call it within your test, you MUST also
     * run that test case in a separate process to prevent autoload caching.
     *
     * @param string $type One of the OptionalPackages::INSTALL constants
     */
    protected function setInstallType($type)
    {
        $r = new ReflectionProperty(OptionalPackages::class, 'installType');
        $r->setAccessible(true);
        $r->setValue($type);

        if (! $this->projectRoot) {
            return;
        }

        switch ($type) {
            case OptionalPackages::INSTALL_FLAT:
                $this->setUpAlternateAutoloader('/src/');
                break;
            case OptionalPackages::INSTALL_MODULAR:
                $this->setUpAlternateAutoloader('/src/App/src/', true);
                break;
        }
    }

    protected function getContainer()
    {
        if (! $this->container) {
            $path = $this->projectRoot
                ? $this->projectRoot . '/config/container.php'
                : 'config/container.php';

            /** @var ContainerInterface $container */
            $this->container = require $path;
        }

        return $this->container;
    }

    protected function getAppResponse($path = '/', $setupRoutes = true)
    {
        $container = $this->getContainer();

        /** @var Application $app */
        $app = $container->get(Application::class);

        // Import programmatic/declarative middleware pipeline and routing configuration statements
        $app->pipe(ErrorHandler::class);
        $app->pipe(ServerUrlMiddleware::class);
        $app->pipeRoutingMiddleware();
        $app->pipe(ImplicitHeadMiddleware::class);
        $app->pipe(ImplicitOptionsMiddleware::class);
        $app->pipe(UrlHelperMiddleware::class);
        $app->pipeDispatchMiddleware();
        $app->pipe(NotFoundHandler::class);

        if ($setupRoutes === true && $container->has(HomePageAction::class)) {
            $app->get('/', HomePageAction::class, 'home');
        }

        if ($setupRoutes === true && $container->has(PingAction::class)) {
            $app->get('/api/ping', PingAction::class, 'api.ping');
        }

        return $app(
            new ServerRequest([], [], 'https://example.com' . $path, 'GET'),
            new Response()
        );
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
