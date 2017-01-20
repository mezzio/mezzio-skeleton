<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\BasePackage;
use Composer\Package\RootPackage;
use ExpressiveInstaller\OptionalPackages;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;
use ReflectionProperty;

abstract class OptionalPackagesTestCase extends TestCase
{
    /**
     * @var Composer|ObjectProphecy
     */
    protected $composer;

    /**
     * @var array Array version of composer.json
     */
    protected $composerData;

    /**
     * @var IOInterface|ObjectProphecy
     */
    protected $io;

    /**
     * @var string Root of this package.
     */
    protected $packageRoot;

    /**
     * @var RootPackage|ObjectProphecy
     */
    protected $rootPackage;

    /**
     * Assert that the installer contains a specification for the package.
     *
     * @param string $package
     * @param OptionalPackages $installer
     * @param null|string $message
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public static function assertPackage($package, OptionalPackages $installer, $message = null)
    {
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
     * @param string $package
     * @param OptionalPackages $installer
     * @param null|string $message
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public static function assertNotPackage($package, OptionalPackages $installer, $message = null)
    {
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
     * @param OptionalPackages $installer
     * @param null|string $message
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public static function assertPackages(array $packages, OptionalPackages $installer, $message = null)
    {
        foreach ($packages as $package) {
            self::assertPackage($package, $installer, $message);
        }
    }

    /**
     * Assert that the installer contains a specification for each package in the list.
     *
     * @param string[] $packages
     * @param OptionalPackages $installer
     * @param null|string $message
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public static function assertNotPackages(array $packages, OptionalPackages $installer, $message = null)
    {
        foreach ($packages as $package) {
            self::assertNotPackage($package, $installer, $message);
        }
    }

    protected function setUp()
    {
        $this->packageRoot = realpath(__DIR__ . '/../../');
        putenv('COMPOSER=' . $this->packageRoot . '/composer.json');
    }

    protected function tearDown()
    {
        putenv('COMPOSER=');
    }

    /**
     * Create the OptionalPackages installer instance.
     *
     * Creates the IOInterface and Composer mock instances when doing so,
     * and uses the provided $projectRoot, if specified.
     *
     * @param null|string $projectRoot
     * @return OptionalPackages
     */
    protected function createOptionalPackages($projectRoot = null)
    {
        $projectRoot = $projectRoot ?: $this->packageRoot;

        $this->io = $this->prophesize(IOInterface::class);
        return new OptionalPackages(
            $this->io->reveal(),
            $this->createComposer()->reveal(),
            $projectRoot
        );
    }

    protected function createComposer()
    {
        $this->composer = $composer = $this->prophesize(Composer::class);
        $composer->getPackage()->will([$this->createRootPackage(), 'reveal']);
        return $composer;
    }

    protected function createRootPackage()
    {
        $composerJson       = file_get_contents($this->packageRoot . '/composer.json');
        $this->composerJson = $composer = json_decode($composerJson, true);
        $this->rootPackage  = $package = $this->prophesize(RootPackage::class);

        $package->getRequires()->willReturn($composer['require']);
        $package->getDevRequires()->willReturn($composer['require-dev']);
        $package->getStabilityFlags()->willReturn($this->getStabilityFlags());

        return $package;
    }

    protected function getStabilityFlags()
    {
        $r = new ReflectionClass(OptionalPackages::class);
        $properties = $r->getDefaultProperties();
        return array_fill_keys($properties['devDependencies'], BasePackage::STABILITY_DEV);
    }

    /**
     * Retrieve a single property value from the installer.
     *
     * @param OptionalPackages $installer
     * @param string $property
     * @return mixed
     */
    protected function getInstallerProperty(OptionalPackages $installer, $property)
    {
        $r = new ReflectionProperty($installer, $property);
        $r->setAccessible(true);
        return $r->getValue($installer);
    }

    /**
     * Retrieve the stored composer data structure from an installer instance.
     *
     * @param OptionalPackages $instance
     * @return array
     */
    protected function getComposerDataFromInstaller(OptionalPackages $installer)
    {
        return $this->getInstallerProperty($installer, 'composerDefinition');
    }

    /**
     * Retrieve the stored resource configuration from an installer instance.
     *
     * @param OptionalPackages $instance
     * @return array
     */
    protected function getInstallerConfig(OptionalPackages $installer)
    {
        return $this->getInstallerProperty($installer, 'config');
    }
}
