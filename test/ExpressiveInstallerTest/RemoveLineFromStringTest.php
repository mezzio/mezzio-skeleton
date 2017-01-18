<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

class RemoveLineFromStringTest extends OptionalPackagesTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->installer = $this->createOptionalPackages();
    }

    public function testRemoveFirstLine()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual = $this->installer->removeLinesContainingStrings(['foo'], $string);
        $expected = "bar\nbaz\nqux\nquux";

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveSingleLine()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual = $this->installer->removeLinesContainingStrings(['bar'], $string);
        $expected = "foo\nbaz\nqux\nquux";

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveMultipleLines()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual = $this->installer->removeLinesContainingStrings(['bar', 'baz'], $string);
        $expected = "foo\nqux\nquux";

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveLinesWithSpaces()
    {
        $string = "foo\n  bar\n  baz  \n  qux\nquux";

        $actual = $this->installer->removeLinesContainingStrings(['bar', 'baz'], $string);
        $expected = "foo\n  qux\nquux";

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveLastLine()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual = $this->installer->removeLinesContainingStrings(['quux'], $string);
        $expected = "foo\nbar\nbaz\nqux\n";

        $this->assertEquals($expected, $actual);
    }
}
