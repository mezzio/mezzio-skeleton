<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;

use function chdir;

class ProcessAnswersTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /** @var OptionalPackages */
    private $installer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = $this->copyProjectFilesToTempFilesystem();
        $this->installer   = $this->createOptionalPackages($this->projectRoot);
        $this->prepareSandboxForInstallType(OptionalPackages::INSTALL_MINIMAL, $this->installer);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        chdir($this->packageRoot);
        $this->recursiveDelete($this->projectRoot);
    }

    public function testInvalidAnswer(): void
    {
        $this->io->expects($this->never())->method('write');

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 'foobar';
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertFalse($result);
        self::assertFileDoesNotExist($this->projectRoot . '/config/container.php');
    }

    public function testAnsweredWithN(): void
    {
        $this->io->expects($this->never())->method('write');

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 'n';
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertFalse($result);
        self::assertFileDoesNotExist($this->projectRoot . '/config/container.php');
    }

    public function testAnsweredWithInvalidOption(): void
    {
        $this->io->expects($this->never())->method('write');

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 10;
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertFalse($result);
        self::assertFileDoesNotExist($this->projectRoot . '/config/container.php');
    }

    public function testAnsweredWithValidOption(): void
    {
        // Prepare the installer
        $this->installer->removeDevDependencies();

        $this->io
            ->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                [$this->stringContains('Adding package <info>laminas/laminas-pimple-config</info>')],
                [$this->stringContains('Copying <info>config/container.php</info>')],
            );

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 1;
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertTrue($result);
        self::assertFileExists($this->projectRoot . '/config/container.php');
        self::assertPackage('laminas/laminas-pimple-config', $this->installer);
    }

    public function testAnsweredWithPackage(): void
    {
        // Prepare the installer
        $this->installer->removeDevDependencies();

        $this->io
            ->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                [$this->stringContains('Adding package <info>league/container</info>')],
                [$this->stringContains('<warning>You need to edit public/index.php')],
            );

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 'league/container:2.2.0';
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertTrue($result);
        self::assertFileDoesNotExist($this->projectRoot . '/config/container.php');
        self::assertPackage('league/container', $this->installer);
    }

    public function testPackagesAreAddedToWhitelist(): void
    {
        // Prepare the installer
        $this->installer->removeDevDependencies();

        $this->io
            ->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                [$this->stringContains('Adding package <info>mezzio/mezzio-laminasviewrenderer</info>')],
                [$this->stringContains('Whitelist package <info>mezzio/mezzio-laminasviewrenderer</info>')],
            );

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['template-engine'];
        $answer   = 3;
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertTrue($result);
        self::assertPackage('mezzio/mezzio-laminasviewrenderer', $this->installer);
        self::assertWhitelisted('mezzio/mezzio-laminasviewrenderer', $this->installer);
    }
}
