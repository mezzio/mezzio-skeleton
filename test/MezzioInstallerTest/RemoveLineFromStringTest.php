<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use PHPUnit_Framework_TestCase as TestCase;

class RemoveLineFromStringTest extends TestCase
{
    public function testRemoveFirstLine()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual = OptionalPackages::removeLinesContainingStrings(['foo'], $string);
        $expected = "bar\nbaz\nqux\nquux";

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveSingleLine()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual = OptionalPackages::removeLinesContainingStrings(['bar'], $string);
        $expected = "foo\nbaz\nqux\nquux";

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveMultipleLines()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual = OptionalPackages::removeLinesContainingStrings(['bar', 'baz'], $string);
        $expected = "foo\nqux\nquux";

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveLinesWithSpaces()
    {
        $string = "foo\n  bar\n  baz  \n  qux\nquux";

        $actual = OptionalPackages::removeLinesContainingStrings(['bar', 'baz'], $string);
        $expected = "foo\n  qux\nquux";

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveLastLine()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual = OptionalPackages::removeLinesContainingStrings(['quux'], $string);
        $expected = "foo\nbar\nbaz\nqux\n";

        $this->assertEquals($expected, $actual);
    }
}
