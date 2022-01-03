<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use ReflectionProperty;

class UpdateRootPackageTest extends OptionalPackagesTestCase
{
    /** @var array[] */
    protected $changes = [
        'composerRequires'    => [
            'foo/bar',
            'baz/bat',
        ],
        'composerDevRequires' => [
            'rab/oof',
            'tab/zab',
        ],
        'stabilityFlags'      => [
            'foo/bar' => 'stable',
            'baz/bat' => 'beta',
        ],
        'composerDefinition'  => [
            'autoload'     => [
                'psr-4' => [
                    'MezzioInstaller\\' => __DIR__,
                ],
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'MezzioInstallerTest\\' => __DIR__,
                ],
            ],
        ],
    ];

    public function testUpdateRootPackageWillUpdateComposedPackage()
    {
        $installer = $this->createOptionalPackages();
        $this->setInstallerProperties($installer);

        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->rootPackage->expects($this->atLeastOnce())->method('setRequires')->with($this->changes['composerRequires']);
        $this->rootPackage->expects($this->atLeastOnce())->method('setDevRequires')->with($this->changes['composerDevRequires']);
        $this->rootPackage->expects($this->atLeastOnce())->method('setStabilityFlags')->with($this->changes['stabilityFlags']);
        $this->rootPackage->expects($this->atLeastOnce())->method('setAutoload')->with($this->changes['composerDefinition']['autoload']);
        $this->rootPackage->expects($this->atLeastOnce())->method('setDevAutoload')->with($this->changes['composerDefinition']['autoload-dev']);
        $this->rootPackage->expects($this->atLeastOnce())->method('setExtra')->with([]);
        // phpcs:enable Generic.Files.LineLength.TooLong

        $installer->updateRootPackage();
    }

    protected function setInstallerProperties(OptionalPackages $installer): void
    {
        foreach ($this->changes as $property => $value) {
            $this->setInstallerProperty($installer, $property, $value);
        }
    }

    protected function setInstallerProperty(OptionalPackages $installer, string $property, array $value): void
    {
        $r = new ReflectionProperty($installer, $property);
        $r->setAccessible(true);
        $r->setValue($installer, $value);
    }
}
