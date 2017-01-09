<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class CopyFileTest extends InstallerTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $projectRoot;

    protected function setUp()
    {
        $this->projectRoot = vfsStream::setup('project-root');
    }

    public function testTargetFileDoesNotExist()
    {
        $this->assertFalse($this->projectRoot->hasChild('data'));
        $this->assertFalse($this->projectRoot->hasChild('data/target.php'));
    }

    public function testFileIsCopiedIfItDoesNotExist()
    {
        $io = $this->prophesize('Composer\IO\IOInterface');

        OptionalPackages::copyFile($io->reveal(), vfsStream::url('project-root'), '/config.php', '/data/target.php');

        $this->assertTrue($this->projectRoot->hasChild('data'));
        $this->assertTrue($this->projectRoot->hasChild('data/target.php'));
        $this->assertFileEquals(
            dirname(dirname(__DIR__)) . '/src/ExpressiveInstaller/config.php',
            vfsStream::url('project-root/data/target.php')
        );
    }

    public function testFileIsNotCopiedIfItExists()
    {
        $io = $this->prophesize('Composer\IO\IOInterface');

        // Create default test file
        vfsStream::newFile('data/target.php')->at($this->projectRoot)->setContent('TEST');

        $this->assertTrue($this->projectRoot->hasChild('data/target.php'));

        // Copy file (should not copy file)
        OptionalPackages::copyFile($io->reveal(), vfsStream::url('project-root'), '/config.php', '/data/target.php');

        $this->assertTrue($this->projectRoot->hasChild('data/target.php'));
        $this->assertEquals(
            'TEST',
            file_get_contents(vfsStream::url('project-root/data/target.php'))
        );
    }
}
