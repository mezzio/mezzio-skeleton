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

class RequestInstallTypeTest extends OptionalPackagesTestCase
{
    /**
     * @var OptionalPackages
     */
    private $installer;

    protected function setUp()
    {
        parent::setUp();
        $this->installer = $this->createOptionalPackages();
    }

    public function installSelections() : array
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
            ->ask(Argument::that([__CLASS__, 'assertQueryPrompt']), '2')
            ->willReturn($selection);

        $this->assertSame($expected, $this->installer->requestInstallType());
    }

    public function testWillContinueToPromptUntilValidAnswerPresented()
    {
        $io     = $this->io;
        $tries  = mt_rand(1, 10);

        // Handle a call to ask() by looping $tries times
        $handle = function () use ($io, &$tries, &$handle) {
            if (0 === $tries) {
                // Valid choice to complete the loop
                return '1';
            }

            // Otherwise, ask again.
            $tries -= 1;
            $io->ask(Argument::that([__CLASS__, 'assertQueryPrompt']), '2')->will($handle);
            return 'n';
        };

        $this->io
            ->ask(Argument::that([__CLASS__, 'assertQueryPrompt']), '2')
            ->will($handle);

        $this->io
            ->write(Argument::containingString('Invalid answer'))
            ->shouldBeCalledTimes($tries);

        $this->assertSame(OptionalPackages::INSTALL_MINIMAL, $this->installer->requestInstallType());
        $this->assertEquals(0, $tries);
    }

    public static function assertQueryPrompt($value)
    {
        self::assertInternalType(
            'string',
            $value,
            'Questions must be a string since symfony/console:4.0'
        );

        self::assertThat(
            false !== strpos($value, 'What type of installation would you like?'),
            self::isTrue(),
            'Unexpected prompt value'
        );

        return true;
    }
}
