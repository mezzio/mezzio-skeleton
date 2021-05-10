<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;
use Prophecy\Argument;

use function random_int;
use function strpos;

class RequestInstallTypeTest extends OptionalPackagesTestCase
{
    /** @var OptionalPackages */
    private $installer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->installer = $this->createOptionalPackages();
    }

    public function installSelections(): array
    {
        return [
            OptionalPackages::INSTALL_MINIMAL => ['1', OptionalPackages::INSTALL_MINIMAL],
            OptionalPackages::INSTALL_FLAT    => ['2', OptionalPackages::INSTALL_FLAT],
            OptionalPackages::INSTALL_MODULAR => ['3', OptionalPackages::INSTALL_MODULAR],
        ];
    }

    /**
     * @dataProvider installSelections
     */
    public function testRequestInstallTypeReturnsExpectedConstantValue(string $selection, string $expected)
    {
        $this->io
            ->ask(Argument::that([self::class, 'assertQueryPrompt']), '2')
            ->willReturn($selection);

        self::assertSame($expected, $this->installer->requestInstallType());
    }

    public function testWillContinueToPromptUntilValidAnswerPresented()
    {
        $io    = $this->io;
        $tries = random_int(1, 10);

        // Handle a call to ask() by looping $tries times
        $handle = function () use ($io, &$tries, &$handle) {
            if ($tries === 0) {
                // Valid choice to complete the loop
                return '1';
            }

            // Otherwise, ask again.
            $tries -= 1;
            $io->ask(Argument::that([self::class, 'assertQueryPrompt']), '2')->will($handle);
            return 'n';
        };

        $this->io
            ->ask(Argument::that([self::class, 'assertQueryPrompt']), '2')
            ->will($handle);

        $this->io
            ->write(Argument::containingString('Invalid answer'))
            ->shouldBeCalledTimes($tries);

        self::assertSame(OptionalPackages::INSTALL_MINIMAL, $this->installer->requestInstallType());
        self::assertEquals(0, $tries);
    }

    /**
     * @param mixed $value
     */
    public static function assertQueryPrompt($value): bool
    {
        self::assertIsString(
            $value,
            'Questions must be a string since symfony/console:4.0'
        );

        self::assertThat(
            strpos($value, 'What type of installation would you like?') !== false,
            self::isTrue(),
            'Unexpected prompt value'
        );

        return true;
    }
}
