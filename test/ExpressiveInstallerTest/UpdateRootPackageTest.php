<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use ReflectionProperty;

class UpdateRootPackageTest extends OptionalPackagesTestCase
{
    /**
     * @var array[]
     */
    protected $changes = [
        'composerRequires' => [
            'foo/bar',
            'baz/bat',
        ],
        'composerDevRequires' => [
            'rab/oof',
            'tab/zab',
        ],
        'stabilityFlags' => [
            'foo/bar' => 'stable',
            'baz/bat' => 'beta',
        ],
        'composerDefinition' => [
            'autoload' => [
                'psr-4' => [
                    'ExpressiveInstaller\\' => __DIR__,
                ],
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'ExpressiveInstallerTest\\' => __DIR__,
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

        $installer->updateRootPackage();
    }

    protected function setInstallerProperties(OptionalPackages $installer)
    {
        foreach ($this->changes as $property => $value) {
            $this->setInstallerProperty($installer, $property, $value);
        }
    }

    protected function setInstallerProperty(OptionalPackages $installer, $property, $value)
    {
        $r = new ReflectionProperty($installer, $property);
        $r->setAccessible(true);
        $r->setValue($installer, $value);
    }
}
