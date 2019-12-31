<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use PHPUnit_Framework_TestCase as TestCase;

class RemoveComposerLockTest extends TestCase
{
    public function testRemoveLineFromString()
    {
        $string = "foo\nbar\nbaz";

        $actual = OptionalPackages::removeLineFromString('bar', $string);
        $expected = "foo\nbaz";

        $this->assertEquals($expected, $actual);
    }
}
