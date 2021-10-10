<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use MezzioInstaller\OptionalPackages;

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
    public function testRequestInstallTypeReturnsExpectedConstantValue(string $selection, string $expected): void
    {
        $this->io
            ->method('ask')
            ->with(self::callback([self::class, 'assertQueryPrompt']), '2')
            ->willReturn($selection);

        self::assertSame($expected, $this->installer->requestInstallType());
    }

    public function testWillContinueToPromptUntilValidAnswerPresented(): void
    {
        $tries = random_int(1, 10);

        $questionAssertions = [
            self::stringContains('What type of installation would you like?'),
            [self::class, 'assertQueryPrompt'],
        ];

        for ($i = 1; $i <= $tries; $i++) {
            $questionAssertions[] = [self::class, 'assertQueryPrompt'];
        }

        // Handle a call to ask() by looping $tries times
        $handle = function (string $question) use (&$tries) {
            // phpcs:disable WebimpressCodingStandard.Formatting.RedundantParentheses.SingleExpression
            [self::class, 'assertQueryPrompt']($question);
            // phpcs:enable WebimpressCodingStandard.Formatting.RedundantParentheses.SingleExpression

            if ($tries === 0) {
                // Valid choice to complete the loop
                return '1';
            }

            // Otherwise, ask again.
            $tries -= 1;
            return 'n';
        };

        $this->io
            ->method('ask')
            ->willReturnCallback($handle);

        $this->io
            ->expects(self::exactly($tries))
            ->method('write')
            ->with(self::stringContains('Invalid answer'));

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
