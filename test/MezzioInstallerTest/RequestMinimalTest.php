<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use Prophecy\Argument;

class RequestMinimalTest extends InstallerTestCase
{
    public function testRequestMinimalInstallIsTrue()
    {
        $io = $this->prophesize('Composer\IO\IOInterface');
        $io->ask(Argument::any(), Argument::any())->willReturn('y');

        $answer = OptionalPackages::requestMinimal($io->reveal());
        $this->assertTrue($answer);
    }

    public function testRequestMinimalInstallIsFalse()
    {
        $io = $this->prophesize('Composer\IO\IOInterface');
        $io->ask(Argument::any(), Argument::any())->willReturn('n');

        $answer = OptionalPackages::requestMinimal($io->reveal());
        $this->assertFalse($answer);
    }
}
