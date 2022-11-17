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
use function str_contains;

class PromptForOptionalPackagesTest extends OptionalPackagesTestCase
{
    use ProjectSandboxTrait;

    private OptionalPackages $installer;

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

    /**
     * @psalm-return Generator<string, array{
     *     0: string,
     *     1: string,
     *     2: int,
     *     3: string
     * }>
     */
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
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->callback(static function ($arg) use ($question): bool {
                    PromptForOptionalPackagesTest::assertPromptText($question['question'], $arg);

                    return true;
                }),
                $question['default']
            )
            ->willReturn($selection);

        $toWrite = [];
        $written = [];

        foreach ($expectedPackage['packages'] as $package) {
            $toWrite[] = $package;
        }

        foreach ($expectedPackage[OptionalPackages::INSTALL_MINIMAL] as $target) {
            $toWrite[] = $target;
        }

        if ($toWrite !== []) {
            $this->io
                ->method('write')
                ->with($this->callback(static function (string $message) use ($toWrite, &$written): bool {
                    foreach ($toWrite as $package) {
                        if (! str_contains($message, $package)) {
                            continue;
                        }

                        if (
                            str_contains($message, 'Adding package')
                            || str_contains($message, '- Copying ')
                        ) {
                            $written[] = $package;
                        }

                        return true;
                    }
                    return false;
                }));
        }

        self::assertNull($this->installer->promptForOptionalPackage($questionName, $question));
        self::assertSame(count($written), count($toWrite));
    }

    public static function assertPromptText(string $expected, mixed $argument): void
    {
        self::assertIsString(
            $argument,
            'Questions must be a string since symfony/console:4.0'
        );

        self::assertThat(
            str_contains($argument, $expected),
            self::isTrue(),
            sprintf('Expected prompt not received: "%s"', $expected)
        );
    }
}
