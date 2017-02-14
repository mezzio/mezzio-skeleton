<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-installer for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-installer/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use App\Action\HomePageAction;
use App\Action\PingAction;
use DirectoryIterator;
use ExpressiveInstaller\OptionalPackages;
use Interop\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Middleware\NotFoundHandler;
use Zend\Stratigility\Middleware\ErrorHandler;

trait ProjectSandboxTrait
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
     * @var string Root of the sandbox system
     */
    protected $projectRoot;

    /**
     * Copies the project files into a temporary filesystem.
     *
     * Sets the path to the new temporary filesystem in the $projectRoot
     * property, changes the working directory to that new location, and
     * returns the location.
     *
     * cleanup() recursively removes the created directory.
     */
    protected function copyProjectFilesToTempFilesystem()
    {
        $this->projectRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('exp');

        mkdir($this->projectRoot . '/data', 0777, true);
        foreach (['config', 'public', 'src', 'templates', 'test'] as $path) {
            $this->recursiveCopy(
                $this->packageRoot . DIRECTORY_SEPARATOR . $path,
                $this->projectRoot . DIRECTORY_SEPARATOR . $path
            );
        }

        chdir($this->projectRoot);
        return $this->projectRoot;
    }

    /**
     * Prepare the sandbox for the selected instalation type.
     *
     * Sets the installer's install type, and sets up the application structure.
     *
     * If a non-minimal install type is selected, also sets up the alternate
     * autoloader to ensure the `App` namespace resolves correctly.
     *
     * @param string $installType
     * @param OptionalPackages $installer
     */
    protected function prepareSandboxForInstallType($installType, OptionalPackages $installer)
    {
        $installer->setInstallType($installType);
        $installer->setupDefaultApp($installType);

        switch ($installType) {
            case OptionalPackages::INSTALL_FLAT:
                $this->setUpAlternateAutoloader('/src/');
                break;
            case OptionalPackages::INSTALL_MODULAR:
                $this->setUpAlternateAutoloader('/src/App/src/', true);
                break;
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

    /**
     * Remove the alternate autolader, if present.
     */
    protected function tearDownAlternateAutoloader()
    {
        if ($this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            unset($this->autoloader);
        }
    }

    /**
     * Returns the configured container for the sandbox project.
     *
     * @return ContainerInterface;
     */
    protected function getContainer()
    {
        if ($this->container) {
            return $this->container;
        }

        $path = $this->projectRoot
            ? $this->projectRoot . '/config/container.php'
            : 'config/container.php';

        /** @var ContainerInterface $container */
        $this->container = require $path;

        return $this->container;
    }

    /**
     * Creates and dispatches an application at the requested path.
     *
     * @param string $path Path to request within the application
     * @param bool $setupRoutes Whether or not to setup routes before dispatch
     * @return Response
     */
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
            new Response(),
            $app->getDefaultDelegate()
        );
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

            if ($fileInfo->isDir()) {
                $path = $fileInfo->getFilename();
                if (in_array($path, ['.', '..'], true)) {
                    continue;
                }

                mkdir($target . '/' . $path, 0777, true);

                $this->recursiveCopy(
                    $source . DIRECTORY_SEPARATOR . $path,
                    $target . DIRECTORY_SEPARATOR . $path
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
}
