<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
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
