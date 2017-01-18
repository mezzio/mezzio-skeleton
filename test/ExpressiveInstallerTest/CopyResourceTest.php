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

class CopyResourceTest extends OptionalPackagesTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    protected $project;

    /**
     * @var string URL of project root
     */
    protected $projectRoot;

    protected function setUp()
    {
        parent::setUp();
        $this->project = vfsStream::setup('project-root');
        $this->projectRoot = vfsStream::url('project-root');
    }

    public function testTargetFileDoesNotExist()
    {
        $this->assertFalse($this->project->hasChild('data'));
        $this->assertFalse($this->project->hasChild('data/target.php'));
    }

    public function testResourceIsCopiedIfItDoesNotExist()
    {
        $installer = $this->createOptionalPackages($this->projectRoot);

        $installer->copyResource('config.php', 'data/target.php');

        $this->assertTrue($this->project->hasChild('data'));
        $this->assertTrue($this->project->hasChild('data/target.php'));
        $this->assertFileEquals(
            $this->packageRoot . '/src/ExpressiveInstaller/config.php',
            vfsStream::url('project-root/data/target.php')
        );
    }

    public function testResourceIsNotCopiedIfItExists()
    {
        // Create default test file
        vfsStream::newFile('data/target.php')
            ->at($this->project)
            ->setContent('TEST');

        $this->assertTrue($this->project->hasChild('data/target.php'));

        // Copy file (should not copy file)
        $installer = $this->createOptionalPackages($this->projectRoot);
        $installer->copyResource('config.php', 'data/target.php');

        $this->assertTrue($this->project->hasChild('data/target.php'));
        $this->assertEquals(
            'TEST',
            file_get_contents(vfsStream::url('project-root/data/target.php'))
        );
    }
}
