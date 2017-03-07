<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-skeleton for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-skeleton/blob/master/LICENSE.md New BSD License
 */

namespace ExpressiveInstallerTest;

use ExpressiveInstaller\OptionalPackages;
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

        $this->installer   = $this->createOptionalPackages($this->projectRoot);
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
        $config = require __DIR__ . '/../../src/ExpressiveInstaller/config.php';
        foreach ($config['questions'] as $questionName => $question) {
            foreach ($question['options'] as $selection => $package) {
                $name = sprintf('%s-%s', $questionName, $package['name']);
                yield $name => [$questionName, $question, $selection, $package];
            }
        }
    }

    /**
     * @dataProvider promptCombinations
     *
     * @param string $questionName
     * @param array $question
     * @param int $selection
     * @param array $expectedPackage
     */
    public function testPromptForOptionalPackage($questionName, array $question, $selection, array $expectedPackage)
    {
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
        $message  = sprintf('Expected prompt not received: "%s"', $expected);
        self::assertThat(false !== strpos($argument, $expected), self::isTrue(), $message);
    }
}
