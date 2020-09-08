<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioInstallerTest;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use function file_get_contents;

class CopyResourceTest extends OptionalPackagesTestCase
{
    /** @var vfsStreamDirectory */
    private $project;

    /** @var string URL of project root */
    private $projectRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->project     = vfsStream::setup('project-root');
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
            $this->packageRoot . '/src/MezzioInstaller/config.php',
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
