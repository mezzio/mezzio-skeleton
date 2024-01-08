<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use Composer\Package\BasePackage;
use ReflectionProperty;

use function assert;

class AddPackageTest extends OptionalPackagesTestCase
{
    /**
     * @dataProvider packageProvider
     */
    public function testAddPackage(string $packageName, string $packageVersion, ?int $expectedStability): void
    {
        $installer = $this->createOptionalPackages();

        $invocationCount = 0;

        $this->io
            ->expects($this->atLeast(2))
            ->method('write')
            ->with(self::callback(static function (string $value) use (&$invocationCount): bool {
                assert($invocationCount === 0 || $invocationCount === 1);
                $expect = [
                    0 => 'Removing installer development dependencies',
                    1 => 'Adding package',
                ];
                self::assertStringContainsString($expect[$invocationCount], $value);
                $invocationCount++;
                return true;
            }));

        $installer->removeDevDependencies();
        $installer->addPackage($packageName, $packageVersion);

        self::assertPackage('laminas/laminas-stdlib', $installer);

        $r = new ReflectionProperty($installer, 'stabilityFlags');

        $stabilityFlags = $r->getValue($installer);

        // Stability flags are only set for non-stable packages
        if ($expectedStability) {
            self::assertArrayHasKey($packageName, $stabilityFlags);
            self::assertEquals($expectedStability, $stabilityFlags[$packageName]);
        }
    }

    public static function packageProvider(): array
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
