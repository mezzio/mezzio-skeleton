<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use Generator;
use MezzioInstaller\OptionalPackages;

use function chdir;
use function copy;
use function count;
use function putenv;
use function sprintf;
use function strpos;

class PromptForOptionalPackagesTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    /** @var OptionalPackages */
    private $installer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectRoot = $this->copyProjectFilesToTempFilesystem();

        // This test suite writes to the composer.json, so we need to use a copy.
        copy($this->packageRoot . '/composer.json', $this->projectRoot . '/composer.json');
        putenv('COMPOSER=' . $this->projectRoot . '/composer.json');

        $this->installer = $this->createOptionalPackages($this->projectRoot);
        $this->prepareSandboxForInstallType(OptionalPackages::INSTALL_MINIMAL, $this->installer);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        chdir($this->packageRoot);
        $this->recursiveDelete($this->projectRoot);
    }

    public function promptCombinations(): Generator
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
    ): void {
        $this->io
            ->method('ask')
            ->with(
                self::callback(static function ($arg) use ($question) {
                    PromptForOptionalPackagesTest::assertPromptText($question['question'], $arg);

                    return true;
                }),
                $question['default']
            )
            ->willReturn($selection);

        $messageAssertionsToCheck = [];
        foreach ($expectedPackage['packages'] as $package) {
            $messageAssertionsToCheck[] = $this->stringContains($package);
        }

        foreach ($expectedPackage[OptionalPackages::INSTALL_MINIMAL] as $target) {
            $messageAssertionsToCheck[] = $this->stringContains($target);
        }

        $this->io
            ->expects(self::atLeast(count($messageAssertionsToCheck)))
            ->method('write')
            ->with(self::callback(
                static function (string $message) use (&$messageAssertionsToCheck): bool {
                    foreach ($messageAssertionsToCheck as $index => $assertion) {
                        if (! $assertion->evaluate($message, '', true)) {
                            continue;
                        }

                        unset($messageAssertionsToCheck[$index]);
                    }

                    return true;
                }
            ));

        $this->installer->promptForOptionalPackage($questionName, $question);
        self::assertCount(0, $messageAssertionsToCheck, 'Some messages were not written to IO!');
    }

    public static function assertPromptText(string $expected, string $argument): void
    {
        self::assertThat(
            strpos($argument, $expected) !== false,
            self::isTrue(),
            sprintf('Expected prompt not received: "%s"', $expected)
        );
    }
}
