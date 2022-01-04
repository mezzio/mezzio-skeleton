<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\BasePackage;
use Composer\Package\RootPackage;
use MezzioInstaller\OptionalPackages;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

use function array_fill_keys;
use function array_key_exists;
use function file_get_contents;
use function in_array;
use function json_decode;
use function putenv;
use function realpath;
use function sprintf;

// phpcs:ignore WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix
abstract class OptionalPackagesTestCase extends TestCase
{
    /** @var Composer&MockObject */
    protected $composer;

    /** @var array Array version of composer.json */
    protected $composerData;

    /** @var IOInterface&MockObject */
    protected $io;

    /** @var string Root of this package. */
    protected $packageRoot;

    /** @var RootPackage&MockObject */
    protected $rootPackage;

    /**
     * Assert that the installer contains a specification for the package.
     *
     * @throws AssertionFailedError
     */
    public static function assertPackage(
        string $package,
        OptionalPackages $installer,
        ?string $message = null
    ): void {
        $message = $message ?: sprintf('Failed asserting that package "%s" is present in the installer', $package);
        $found   = false;

        foreach (['composerRequires', 'composerDevRequires'] as $property) {
            $r = new ReflectionProperty($installer, $property);
            $r->setAccessible(true);
            if (array_key_exists($package, $r->getValue($installer))) {
                $found = true;
                break;
            }
        }

        self::assertThat($found, self::isTrue(), $message);
    }

    /**
     * Assert that the installer DOES NOT contain a specification for the package.
     *
     * @throws AssertionFailedError
     */
    public static function assertNotPackage(
        string $package,
        OptionalPackages $installer,
        ?string $message = null
    ): void {
        $message = $message ?: sprintf('Failed asserting that package "%s" is absent from the installer', $package);
        $found   = false;

        foreach (['composerRequires', 'composerDevRequires'] as $property) {
            $r = new ReflectionProperty($installer, $property);
            $r->setAccessible(true);
            if (array_key_exists($package, $r->getValue($installer))) {
                $found = true;
                break;
            }
        }

        self::assertThat($found, self::isFalse(), $message);
    }

    /**
     * Assert that the installer DOES NOT contain a specification for each package in the list.
     *
     * @param string[] $packages
     * @throws AssertionFailedError
     */
    public static function assertPackages(
        array $packages,
        OptionalPackages $installer,
        ?string $message = null
    ): void {
        foreach ($packages as $package) {
            self::assertPackage($package, $installer, $message);
        }
    }

    /**
     * Assert that the installer contains a specification for each package in the list.
     *
     * @param string[] $packages
     * @throws AssertionFailedError
     */
    public static function assertNotPackages(
        array $packages,
        OptionalPackages $installer,
        ?string $message = null
    ): void {
        foreach ($packages as $package) {
            self::assertNotPackage($package, $installer, $message);
        }
    }

    /**
     * Assert that the installer contains a specification for the package.
     *
     * @throws AssertionFailedError
     */
    public static function assertWhitelisted(
        string $package,
        OptionalPackages $installer,
        ?string $message = null
    ): void {
        $message = $message ?: sprintf('Failed asserting that package "%s" is whitelisted in composer.json', $package);
        $found   = false;

        $r = new ReflectionProperty($installer, 'composerDefinition');
        $r->setAccessible(true);
        $whitelist = $r->getValue($installer)['extra']['laminas']['component-whitelist'];

        if (in_array($package, $whitelist)) {
            $found = true;
        }

        self::assertThat($found, self::isTrue(), $message);
    }

    protected function setUp(): void
    {
        $this->packageRoot = realpath(__DIR__ . '/../../');
        putenv('COMPOSER=' . $this->packageRoot . '/composer.json');
    }

    protected function tearDown(): void
    {
        putenv('COMPOSER=');
    }

    /**
     * Create the OptionalPackages installer instance.
     *
     * Creates the IOInterface and Composer mock instances when doing so,
     * and uses the provided $projectRoot, if specified.
     */
    protected function createOptionalPackages(?string $projectRoot = null): OptionalPackages
    {
        $projectRoot = $projectRoot ?: $this->packageRoot;
        $this->io    = $this->createMock(IOInterface::class);
        return new OptionalPackages(
            $this->io,
            $this->createComposer(),
            $projectRoot
        );
    }

    /**
     * @return Composer&MockObject
     */
    protected function createComposer()
    {
        $this->composer = $this->createMock(Composer::class);
        $this->composer->method('getPackage')->will($this->returnCallback(function () {
            return $this->createRootPackage();
        }));

        return $this->composer;
    }

    /**
     * @return RootPackage&MockObject
     */
    protected function createRootPackage()
    {
        $composerJson      = json_decode(file_get_contents($this->packageRoot . '/composer.json'), true);
        $this->rootPackage = $this->createMock(RootPackage::class);

        $this->rootPackage->method('getRequires')->willReturn($composerJson['require']);
        $this->rootPackage->method('getDevRequires')->willReturn($composerJson['require-dev']);
        $this->rootPackage->method('getStabilityFlags')->willReturn($this->getStabilityFlags());

        return $this->rootPackage;
    }

    protected function getStabilityFlags(): array
    {
        $r          = new ReflectionClass(OptionalPackages::class);
        $properties = $r->getDefaultProperties();

        return array_fill_keys($properties['devDependencies'], BasePackage::STABILITY_DEV);
    }

    /**
     * Retrieve a single property value from the installer.
     *
     * @return mixed
     */
    protected function getInstallerProperty(OptionalPackages $installer, string $property)
    {
        $r = new ReflectionProperty($installer, $property);
        $r->setAccessible(true);

        return $r->getValue($installer);
    }

    /**
     * Retrieve the stored composer data structure from an installer instance.
     */
    protected function getComposerDataFromInstaller(OptionalPackages $installer): array
    {
        return $this->getInstallerProperty($installer, 'composerDefinition');
    }

    /**
     * Retrieve the stored resource configuration from an installer instance.
     */
    protected function getInstallerConfig(OptionalPackages $installer): array
    {
        return $this->getInstallerProperty($installer, 'config');
    }
}
