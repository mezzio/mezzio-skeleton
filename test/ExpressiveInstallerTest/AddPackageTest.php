<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use Composer\Package\BasePackage;
use Prophecy\Argument;
use ReflectionProperty;

class AddPackageTest extends OptionalPackagesTestCase
{
    /**
     * @dataProvider packageProvider
     *
     * @param string $packageName
     * @param string $packageVersion
     * @param null|int $expectedStability
     */
    public function testAddPackage($packageName, $packageVersion, $expectedStability)
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

        $this->assertPackage('zendframework/zend-stdlib', $installer);

        $r = new ReflectionProperty($installer, 'stabilityFlags');
        $r->setAccessible(true);
        $stabilityFlags = $r->getValue($installer);

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
            'dev'    => ['zendframework/zend-stdlib', '1.0.0-dev', BasePackage::STABILITY_DEV],
            'alpha'  => ['zendframework/zend-stdlib', '1.0.0-alpha2', BasePackage::STABILITY_ALPHA],
            'beta'   => ['zendframework/zend-stdlib', '1.0.0-beta.3', BasePackage::STABILITY_BETA],
            'RC'     => ['zendframework/zend-stdlib', '1.0.0-RC4', BasePackage::STABILITY_RC],
            'stable' => ['zendframework/zend-stdlib', '1.0.0', null],
        ];
    }
}
