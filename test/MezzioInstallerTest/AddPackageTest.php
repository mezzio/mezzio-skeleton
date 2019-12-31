<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use Composer\Package\BasePackage;
use MezzioInstaller\OptionalPackages;
use Prophecy\Argument;

class AddPackageTest extends InstallerTestCase
{
    /**
     * @dataProvider packageProvider
     */
    public function testAddPackage($packageName, $packageVersion, $expectedStability)
    {
        // Prepare the installer
        OptionalPackages::removeDevDependencies();

        $io = $this->prophesize('Composer\IO\IOInterface');
        $io->write(Argument::containingString('Adding package'))->shouldBeCalled();

        OptionalPackages::addPackage($io->reveal(), $packageName, $packageVersion);

        $this->assertComposerHasPackages(['laminas/laminas-stdlib']);
        $stabilityFlags = $this->getStabilityFlags();

        // Stability flags are only set for non-stable packages
        if ($expectedStability) {
            $this->assertArrayHasKey($packageName, $stabilityFlags);
            $this->assertEquals($expectedStability, $stabilityFlags[$packageName]);
        }
    }

    public function packageProvider()
    {
        // $packageName, $packageVersion, $expectedStability
        return [
            'dev'    => ['laminas/laminas-stdlib', '1.0.0-dev', BasePackage::STABILITY_DEV],
            'alpha'  => ['laminas/laminas-stdlib', '1.0.0-alpha2', BasePackage::STABILITY_ALPHA],
            'beta'   => ['laminas/laminas-stdlib', '1.0.0-beta.3', BasePackage::STABILITY_BETA],
            'RC'     => ['laminas/laminas-stdlib', '1.0.0-RC4', BasePackage::STABILITY_RC],
            'stable' => ['laminas/laminas-stdlib', '1.0.0', null],
        ];
    }
}
