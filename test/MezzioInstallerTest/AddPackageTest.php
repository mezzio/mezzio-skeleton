<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioInstallerTest;

use Composer\Package\BasePackage;
use Prophecy\Argument;
use ReflectionProperty;

class AddPackageTest extends OptionalPackagesTestCase
{
    /**
     * @dataProvider packageProvider
     */
    public function testAddPackage(string $packageName, string $packageVersion, ?int $expectedStability)
    {
        $installer = $this->createOptionalPackages();

        $this->io
            ->write(Argument::containingString('Removing installer development dependencies'))
            ->shouldBeCalled();
        $installer->removeDevDependencies();

        $this->io
            ->write(Argument::containingString('Adding package'))
            ->shouldBeCalled();

        $installer->addPackage($packageName, $packageVersion);

        $this->assertPackage('laminas/laminas-stdlib', $installer);

        $r = new ReflectionProperty($installer, 'stabilityFlags');
        $r->setAccessible(true);
        $stabilityFlags = $r->getValue($installer);

        // Stability flags are only set for non-stable packages
        if ($expectedStability) {
            $this->assertArrayHasKey($packageName, $stabilityFlags);
            $this->assertEquals($expectedStability, $stabilityFlags[$packageName]);
        }
    }

    public function packageProvider(): array
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
