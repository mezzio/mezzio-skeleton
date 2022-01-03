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
            ->expects($this->once())
            ->method('ask')
            ->with($this->callback(fn ($value) => $this->assertQueryPrompt($value)), '2')
            ->willReturn($selection);

        self::assertSame($expected, $this->installer->requestInstallType());
    }

    public function testWillContinueToPromptUntilValidAnswerPresented(): void
    {
        $tries = random_int(1, 10);

        $argumentLists = [];
        $results       = [];

        do {
            $argumentLists[] = [$this->callback(fn ($value) => $this->assertQueryPrompt($value)), '2'];
            $results[]       = $tries > 0 ? 'n' : '1';

            $tries -= 1;
        } while ($tries > -1);

        $this->io
            ->expects($this->exactly(count($results)))
            ->method('ask')
            ->withConsecutive(...$argumentLists)
            ->willReturnOnConsecutiveCalls(...$results);

        $this->io
            ->expects($this->exactly(count($results) - 1))
            ->method('write')
            ->with($this->stringContains('Invalid answer'));

        self::assertSame(OptionalPackages::INSTALL_MINIMAL, $this->installer->requestInstallType());
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
