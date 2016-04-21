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

class RemoveComposerLockTest extends \PHPUnit_Framework_TestCase
{
    public function testRemoveLine()
    {
        $string = "foo\nbar\nbaz";

        $actual = OptionalPackages::removeLine('bar', $string);
        $expected = "foo\nbaz";

        $this->assertEquals($expected, $actual);
    }
}
