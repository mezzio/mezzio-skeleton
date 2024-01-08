<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;

use function assert;
use function chdir;

class ProcessAnswersTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    private OptionalPackages $installer;

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

        $count = 0;

        $this->io
            ->expects($this->exactly(2))
            ->method('write')
            ->with(self::callback(static function (string $value) use (&$count): bool {
                $expect = [
                    0 => 'Adding package <info>laminas/laminas-servicemanager</info>',
                    1 => 'Copying <info>config/container.php</info>',
                ];
                assert(isset($expect[$count]));
                self::assertStringContainsString($expect[$count], $value);
                $count++;
                return true;
            }));

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 1;
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertTrue($result);
        self::assertFileExists($this->projectRoot . '/config/container.php');
        self::assertPackage('laminas/laminas-servicemanager', $this->installer);
    }

    public function testAnsweredWithPackage(): void
    {
        // Prepare the installer
        $this->installer->removeDevDependencies();

        $count = 0;

        $this->io
            ->expects($this->exactly(2))
            ->method('write')
            ->with(self::callback(static function (string $value) use (&$count): bool {
                $expect = [
                    0 => 'Adding package <info>league/container</info>',
                    1 => '<warning>You need to edit public/index.php',
                ];

                assert(isset($expect[$count]));
                self::assertStringContainsString($expect[$count], $value);
                $count++;

                return true;
            }));

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

        $count = 0;

        $this->io
            ->expects($this->exactly(2))
            ->method('write')
            ->with(self::callback(static function (string $value) use (&$count): bool {
                $expect = [
                    0 => 'Adding package <info>mezzio/mezzio-laminasviewrenderer</info>',
                    1 => 'Whitelist package <info>mezzio/mezzio-laminasviewrenderer</info>',
                ];

                assert(isset($expect[$count]));
                self::assertStringContainsString($expect[$count], $value);
                $count++;

                return true;
            }));

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['template-engine'];
        $answer   = 3;
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertTrue($result);
        self::assertPackage('mezzio/mezzio-laminasviewrenderer', $this->installer);
        self::assertWhitelisted('mezzio/mezzio-laminasviewrenderer', $this->installer);
    }
}
