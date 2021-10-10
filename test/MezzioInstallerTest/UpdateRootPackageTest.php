<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use ReflectionProperty;

class UpdateRootPackageTest extends OptionalPackagesTestCase
{
    /** @var array<string,array<mixed>> */
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

    public function testUpdateRootPackageWillUpdateComposedPackage(): void
    {
        $installer = $this->createOptionalPackages();
        $this->setInstallerProperties($installer);

        $this->rootPackage
            ->expects(self::once())
            ->method('setRequires')
            ->with($this->changes['composerRequires']);

        $this->rootPackage
            ->expects(self::once())
            ->method('setDevRequires')
            ->with($this->changes['composerDevRequires']);

        $this->rootPackage
            ->expects(self::once())
            ->method('setStabilityFlags')
            ->with($this->changes['stabilityFlags']);

        $this->rootPackage
            ->expects(self::once())
            ->method('setAutoload')
            ->with($this->changes['composerDefinition']['autoload']);

        $this->rootPackage
            ->expects(self::once())
            ->method('setDevAutoload')
            ->with($this->changes['composerDefinition']['autoload-dev']);

        $this->rootPackage
            ->expects(self::once())
            ->method('setExtra')
            ->with([]);

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
