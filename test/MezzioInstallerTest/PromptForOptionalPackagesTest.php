<?php

/**
 * @see       https://github.com/mezzio/mezzio-skeleton for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use Prophecy\Argument;

class PromptForOptionalPackagesTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /**
     * @var OptionalPackages
     */
    private $installer;

    protected function setUp()
    {
        parent::setUp();

        $this->projectRoot = $this->copyProjectFilesToTempFilesystem();

        // This test suite writes to the composer.json, so we need to use a copy.
        copy($this->packageRoot . '/composer.json', $this->projectRoot . '/composer.json');
        putenv('COMPOSER=' . $this->projectRoot . '/composer.json');

        $this->installer = $this->createOptionalPackages($this->projectRoot);
        $this->prepareSandboxForInstallType(OptionalPackages::INSTALL_MINIMAL, $this->installer);
    }

    protected function tearDown()
    {
        parent::tearDown();
        chdir($this->packageRoot);
        $this->recursiveDelete($this->projectRoot);
    }

    public function promptCombinations()
    {
        $config = require __DIR__ . '/../../src/MezzioInstaller/config.php';
        foreach ($config['questions'] as $questionName => $question) {
            foreach ($question['options'] as $selection => $package) {
                $name = sprintf('%s-%s', $questionName, $package['name']);
                yield $name => [$questionName, $question, $selection, $package];
            }
        }
    }

    /**
     * @dataProvider promptCombinations
     */
    public function testPromptForOptionalPackage(
        string $questionName,
        array $question,
        int $selection,
        array $expectedPackage
    ) {
        $this->io
            ->ask(
                Argument::that(function ($arg) use ($question) {
                    PromptForOptionalPackagesTest::assertPromptText($question['question'], $arg);

                    return true;
                }),
                $question['default']
            )
            ->willReturn($selection);

        foreach ($expectedPackage['packages'] as $package) {
            $this->io
                ->write(Argument::containingString($package))
                ->shouldBeCalled();
        }

        foreach ($expectedPackage[OptionalPackages::INSTALL_MINIMAL] as $target) {
            $this->io
                ->write(Argument::containingString($target))
                ->shouldBeCalled();
        }

        $this->assertNull($this->installer->promptForOptionalPackage($questionName, $question));
    }

    public static function assertPromptText($expected, $argument)
    {
        $argument = is_array($argument) ? array_shift($argument) : $argument;
        $message = sprintf('Expected prompt not received: "%s"', $expected);
        self::assertThat(false !== strpos($argument, $expected), self::isTrue(), $message);
    }
}
