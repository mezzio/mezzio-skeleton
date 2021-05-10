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

        $this->rootPackage->setRequires($this->changes['composerRequires'])->shouldBeCalled();
        $this->rootPackage->setDevRequires($this->changes['composerDevRequires'])->shouldBeCalled();
        $this->rootPackage->setStabilityFlags($this->changes['stabilityFlags'])->shouldBeCalled();
        $this->rootPackage->setAutoload($this->changes['composerDefinition']['autoload'])->shouldBeCalled();
        $this->rootPackage->setDevAutoload($this->changes['composerDefinition']['autoload-dev'])->shouldBeCalled();
        $this->rootPackage->setExtra([])->shouldBeCalled();

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
