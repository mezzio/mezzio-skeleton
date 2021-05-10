<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use App\Handler\HomePageHandler;
use App\Handler\PingHandler;
use DirectoryIterator;
use Laminas\Diactoros\ServerRequest;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use MezzioInstaller\OptionalPackages;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

use function chdir;
use function copy;
use function file_exists;
use function in_array;
use function is_dir;
use function ltrim;
use function mkdir;
use function rmdir;
use function scandir;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function sprintf;
use function str_replace;
use function strpos;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

trait ProjectSandboxTrait
{
    /**
     * @var null|callable Additional autoloader to prepend to stack.
     *     Used when flat install is requested.
     */
    protected $autoloader;

    /** @var ContainerInterface */
    protected $container;

    /** @var string Root of the sandbox system */
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
    protected function copyProjectFilesToTempFilesystem(): string
    {
        $this->projectRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('exp');

        mkdir($this->projectRoot . '/data', 0777, true);
        mkdir($this->projectRoot . '/data/cache', 0777, true);
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
     */
    protected function prepareSandboxForInstallType(string $installType, OptionalPackages $installer): void
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
     * Enable development-mode configuration within the sandbox.
     */
    protected function enableDevelopmentMode(): void
    {
        $target = sprintf(
            '%s%sconfig%sdevelopment.config.php',
            $this->projectRoot,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );
        copy($target . '.dist', $target);

        Assert::assertFileExists($target);

        $target = sprintf(
            '%s%sconfig%sautoload%sdevelopment.local.php',
            $this->projectRoot,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );
        copy($target . '.dist', $target);

        Assert::assertFileExists($target);
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
     * @param bool   $stripNamespace Whether or not to strip the initial
     *       namespace when determining the path (ala PSR-4).
     */
    protected function setUpAlternateAutoloader(string $appPath, bool $stripNamespace = false): void
    {
        $this->autoloader = function ($class) use ($appPath, $stripNamespace) {
            if (strpos($class, 'App\\') !== 0) {
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
    protected function tearDownAlternateAutoloader(): void
    {
        if ($this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            unset($this->autoloader);
        }
    }

    /**
     * Returns the configured container for the sandbox project.
     */
    protected function getContainer(): ContainerInterface
    {
        if ($this->container) {
            return $this->container;
        }

        $path = $this->projectRoot
            ? $this->projectRoot . '/config/container.php'
            : 'config/container.php';

        $this->container = require $path;

        return $this->container;
    }

    /**
     * Creates and dispatches an application at the requested path.
     *
     * @param string $path Path to request within the application
     * @param bool   $setupRoutes Whether or not to setup routes before dispatch
     */
    protected function getAppResponse(string $path = '/', bool $setupRoutes = true): ResponseInterface
    {
        $container = $this->getContainer();

        /** @var Application $app */
        $app = $container->get(Application::class);

        // Import programmatic/declarative middleware pipeline and routing configuration statements
        $app->pipe(ErrorHandler::class);
        $app->pipe(ServerUrlMiddleware::class);
        $app->pipe(RouteMiddleware::class);
        $app->pipe(MethodNotAllowedMiddleware::class);
        $app->pipe(ImplicitHeadMiddleware::class);
        $app->pipe(ImplicitOptionsMiddleware::class);
        $app->pipe(UrlHelperMiddleware::class);
        $app->pipe(DispatchMiddleware::class);
        $app->pipe(NotFoundHandler::class);

        if ($setupRoutes === true && $container->has(HomePageHandler::class)) {
            $app->get('/', HomePageHandler::class, 'home');
        }

        if ($setupRoutes === true && $container->has(PingHandler::class)) {
            $app->get('/api/ping', PingHandler::class, 'api.ping');
        }

        return $app->handle(
            new ServerRequest([], [], 'https://example.com' . $path, 'GET')
        );
    }

    /**
     * Recursively copy the files from one tree to another.
     */
    protected function recursiveCopy(string $source, string $target): void
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
                $path     = ltrim(str_replace($source, '', $realPath), '/\\');
                copy($realPath, sprintf('%s/%s', $target, $path));
                continue;
            }

            if ($fileInfo->isDir() && ! $fileInfo->isDot()) {
                $path = $fileInfo->getFilename();

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
    protected function recursiveDelete(string $target): void
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
