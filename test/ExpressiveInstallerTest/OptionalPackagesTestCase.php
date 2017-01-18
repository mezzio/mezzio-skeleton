<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\RootPackage;
use ExpressiveInstaller\OptionalPackages;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;

abstract class OptionalPackagesTestCase extends TestCase
{
    /**
     * @var Composer|ObjectProphecy
     */
    protected $composer;

    /**
     * @var OptionalPackages
     */
    protected $installer;

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
        $composerJson      = file_get_contents($this->packageRoot . '/composer.json');
        $composer          = json_decode($composerJson, true);
        $this->rootPackage = $package = $this->prophesize(RootPackage::class);

        $package->getRequires()->willReturn($composer['require']);
        $package->getDevRequires()->willReturn($composer['require-dev']);
        $package->getStabilityFlags()->willReturn($this->getStabilityFlags());

        return $package;
    }

    protected function getStabilityFlags()
    {
        $r = new ReflectionClass(OptionalPackages::class);
        $properties = $r->getDefaultProperties();
        return array_fill_keys($properties['devDependencies'], true);
    }
}
