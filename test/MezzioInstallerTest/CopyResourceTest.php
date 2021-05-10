<?php

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
        self::assertFalse($this->project->hasChild('data'));
        self::assertFalse($this->project->hasChild('data/target.php'));
    }

    public function testResourceIsCopiedIfItDoesNotExist()
    {
        $installer = $this->createOptionalPackages($this->projectRoot);

        $installer->copyResource('config.php', 'data/target.php');

        self::assertTrue($this->project->hasChild('data'));
        self::assertTrue($this->project->hasChild('data/target.php'));
        self::assertFileEquals(
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

        self::assertTrue($this->project->hasChild('data/target.php'));

        // Copy file (should not copy file)
        $installer = $this->createOptionalPackages($this->projectRoot);
        $installer->copyResource('config.php', 'data/target.php');

        self::assertTrue($this->project->hasChild('data/target.php'));
        self::assertEquals(
            'TEST',
            file_get_contents(vfsStream::url('project-root/data/target.php'))
        );
    }
}
