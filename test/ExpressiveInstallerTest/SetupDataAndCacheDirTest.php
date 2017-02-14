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
use Prophecy\Argument;

class SetupDataAndCacheDirTest extends OptionalPackagesTestCase
{
    /**
     * @var OptionalPackages
     */
    private $installer;

    /**
     * @var vfsStreamDirectory
     */
    private $project;

    /**
     * @var string URL of project root
     */
    private $projectRoot;

    protected function setUp()
    {
        parent::setUp();
        $this->project = vfsStream::setup('project-root');
        $this->projectRoot = vfsStream::url('project-root');
        $this->installer = $this->createOptionalPackages($this->projectRoot);
    }

    public function testCreatesDataDirectoryWhenInvoked()
    {
        $this->io->write(Argument::containingString('Setup data and cache dir'))->shouldBeCalled();
        $this->installer->setupDataAndCacheDir();

        // '40755' is the octal representation of the file permissions
        $this->assertTrue(is_dir($this->projectRoot . '/data'), 'Data directory was not created?');
        $this->assertSame(
            '40775',
            sprintf('%o', fileperms($this->projectRoot . '/data')),
            'Data directory permissions incorrect'
        );

        $this->assertTrue(is_dir($this->projectRoot . '/data/cache'), 'Cache directory was not created?');
        $this->assertSame(
            '40775',
            sprintf('%o', fileperms($this->projectRoot . '/data/cache')),
            'Cache directory permissions incorrect'
        );
    }
}
