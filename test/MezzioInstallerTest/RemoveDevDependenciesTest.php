<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;

class RemoveDevDependenciesTest extends InstallerTestCase
{
    private $standardDependencies = [
        'php',
        'roave/security-advisories',
        'mezzio/mezzio',
        'mezzio/mezzio-helpers',
        'laminas/laminas-stdlib',
        'phpunit/phpunit',
        'squizlabs/php_codesniffer',
    ];

    private $devDependencies      = [
        'aura/di',
        'composer/composer',
        'filp/whoops',
        'xtreamwayz/pimple-container-interop',
        'mezzio/mezzio-aurarouter',
        'mezzio/mezzio-fastroute',
        'mezzio/mezzio-platesrenderer',
        'mezzio/mezzio-twigrenderer',
        'mezzio/mezzio-laminasrouter',
        'mezzio/mezzio-laminasviewrenderer',
        'laminas/laminas-servicemanager',
    ];

    public function testComposerHasAllDependencies()
    {
        $this->assertComposerHasPackages($this->standardDependencies);
        $this->assertComposerHasPackages($this->devDependencies);
    }

    public function testDevDependenciesAreRemoved()
    {
        // Prepare the installer
        OptionalPackages::removeDevDependencies();

        $this->assertComposerHasPackages($this->standardDependencies);
        $this->assertComposerNotHasPackages($this->devDependencies);
    }
}
