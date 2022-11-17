<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Classes.UnusedPrivateElements.WriteOnlyProperty

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use function fileperms;
use function is_dir;
use function sprintf;

class SetupDataAndCacheDirTest extends OptionalPackagesTestCase
{
    private OptionalPackages $installer;

    private vfsStreamDirectory $project;

    /** @var string URL of project root */
    private string $projectRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->project     = vfsStream::setup('project-root');
        $this->projectRoot = vfsStream::url('project-root');
        $this->installer   = $this->createOptionalPackages($this->projectRoot);
    }

    public function testCreatesDataDirectoryWhenInvoked(): void
    {
        $this->io
            ->expects($this->once())
            ->method('write')
            ->with($this->stringContains('Setup data and cache dir'));
        $this->installer->setupDataAndCacheDir();

        // '40755' is the octal representation of the file permissions
        self::assertTrue(is_dir($this->projectRoot . '/data'), 'Data directory was not created?');
        self::assertSame(
            '40775',
            sprintf('%o', fileperms($this->projectRoot . '/data')),
            'Data directory permissions incorrect'
        );

        self::assertTrue(is_dir($this->projectRoot . '/data/cache'), 'Cache directory was not created?');
        self::assertSame(
            '40775',
            sprintf('%o', fileperms($this->projectRoot . '/data/cache')),
            'Cache directory permissions incorrect'
        );
    }
}
