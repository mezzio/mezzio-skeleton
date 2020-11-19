<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use Prophecy\Argument;

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

    public function testInvalidAnswer()
    {
        $this->io->write()->shouldNotBeCalled();

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 'foobar';
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertFalse($result);
        self::assertFileDoesNotExist($this->projectRoot . '/config/container.php');
    }

    public function testAnsweredWithN()
    {
        $this->io->write()->shouldNotBeCalled();

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 'n';
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertFalse($result);
        self::assertFileDoesNotExist($this->projectRoot . '/config/container.php');
    }

    public function testAnsweredWithInvalidOption()
    {
        $this->io->write()->shouldNotBeCalled();

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 10;
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertFalse($result);
        self::assertFileDoesNotExist($this->projectRoot . '/config/container.php');
    }

    public function testAnsweredWithValidOption()
    {
        // Prepare the installer
        $this->installer->removeDevDependencies();

        // @codingStandardsIgnoreStart
        $this->io->write(Argument::containingString('Adding package <info>laminas/laminas-auradi-config</info>'))->shouldBeCalled();
        $this->io->write(Argument::containingString('Copying <info>config/container.php</info>'))->shouldBeCalled();
        // @codingStandardsIgnoreEnd

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 1;
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertTrue($result);
        self::assertFileExists($this->projectRoot . '/config/container.php');
        self::assertPackage('laminas/laminas-auradi-config', $this->installer);
    }

    public function testAnsweredWithPackage()
    {
        // Prepare the installer
        $this->installer->removeDevDependencies();

        $this->io->write(Argument::containingString('Adding package <info>league/container</info>'))->shouldBeCalled();
        $this->io->write(Argument::containingString('<warning>You need to edit public/index.php'))->shouldBeCalled();

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['container'];
        $answer   = 'league/container:2.2.0';
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertTrue($result);
        self::assertFileDoesNotExist($this->projectRoot . '/config/container.php');
        self::assertPackage('league/container', $this->installer);
    }

    public function testPackagesAreAddedToWhitelist()
    {
        // Prepare the installer
        $this->installer->removeDevDependencies();

        $this->io
            ->write(Argument::containingString(
                'Adding package <info>mezzio/mezzio-laminasviewrenderer</info>'
            ))
            ->shouldBeCalled();
        $this->io
            ->write(Argument::containingString(
                'Whitelist package <info>mezzio/mezzio-laminasviewrenderer</info>'
            ))
            ->shouldBeCalled();

        $config   = $this->getInstallerConfig($this->installer);
        $question = $config['questions']['template-engine'];
        $answer   = 3;
        $result   = $this->installer->processAnswer($question, $answer);

        self::assertTrue($result);
        self::assertPackage('mezzio/mezzio-laminasviewrenderer', $this->installer);
        self::assertWhitelisted('mezzio/mezzio-laminasviewrenderer', $this->installer);
    }
}
