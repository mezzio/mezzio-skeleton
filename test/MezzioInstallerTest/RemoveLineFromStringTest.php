<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;

class RemoveLineFromStringTest extends OptionalPackagesTestCase
{
    /** @var OptionalPackages */
    private $installer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->installer = $this->createOptionalPackages();
    }

    public function testRemoveFirstLine()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual   = $this->installer->removeLinesContainingStrings(['foo'], $string);
        $expected = "bar\nbaz\nqux\nquux";

        self::assertEquals($expected, $actual);
    }

    public function testRemoveSingleLine()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual   = $this->installer->removeLinesContainingStrings(['bar'], $string);
        $expected = "foo\nbaz\nqux\nquux";

        self::assertEquals($expected, $actual);
    }

    public function testRemoveMultipleLines()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual   = $this->installer->removeLinesContainingStrings(['bar', 'baz'], $string);
        $expected = "foo\nqux\nquux";

        self::assertEquals($expected, $actual);
    }

    public function testRemoveLinesWithSpaces()
    {
        $string = "foo\n  bar\n  baz  \n  qux\nquux";

        $actual   = $this->installer->removeLinesContainingStrings(['bar', 'baz'], $string);
        $expected = "foo\n  qux\nquux";

        self::assertEquals($expected, $actual);
    }

    public function testRemoveLastLine()
    {
        $string = "foo\nbar\nbaz\nqux\nquux";

        $actual   = $this->installer->removeLinesContainingStrings(['quux'], $string);
        $expected = "foo\nbar\nbaz\nqux\n";

        self::assertEquals($expected, $actual);
    }
}
