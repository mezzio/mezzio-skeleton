<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use ReflectionClass;

class RemoveDevDependenciesTest extends OptionalPackagesTestCase
{
    /** @var string[] */
    private $standardDependencies = [
        'php',
        'roave/security-advisories',
        'mezzio/mezzio',
        'mezzio/mezzio-helpers',
        'laminas/laminas-stdlib',
        'phpunit/phpunit',
    ];

    /** @var string[] List of dev dependencies intended for removal. */
    private $devDependencies;

    /** @var OptionalPackages */
    private $installer;

    protected function setUp(): void
    {
        parent::setUp();

        // Get list of dev dependencies expected to remove from
        // OptionalPackages class
        $r                     = new ReflectionClass(OptionalPackages::class);
        $props                 = $r->getDefaultProperties();
        $this->devDependencies = $props['devDependencies'];

        $this->installer = $this->createOptionalPackages();
    }

    public function testComposerHasAllDependencies(): void
    {
        self::assertPackages($this->standardDependencies, $this->installer);
        self::assertPackages($this->devDependencies, $this->installer);
    }

    public function testDevDependenciesAreRemoved(): void
    {
        // Remove development dependencies
        $this->installer->removeDevDependencies();

        self::assertPackages($this->standardDependencies, $this->installer);
        self::assertNotPackages($this->devDependencies, $this->installer);
    }
}
