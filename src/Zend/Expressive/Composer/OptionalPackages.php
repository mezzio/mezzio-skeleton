<?php

namespace Zend\Expressive\Composer;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\AliasPackage;
use Composer\Package\Link;
use Composer\Script\Event;

/**
 * Composer installer script
 *
 * Add this script to composer.json:
 *
 *  "scripts": {
 *      "pre-update-cmd": "Zend\\Expressive\\Composer\\OptionalPackages::install",
 *      "pre-install-cmd": "Zend\\Expressive\\Composer\\OptionalPackages::install"
 *  },
 *
 * @package Zend\Expressive\Composer
 *
 * @author Geert Eltink <@xtreamwayz>
 */
class OptionalPackages
{
    const PACKAGE_REGEX = '/^(?P<name>[^:]+\/[^:]+)([:]*)(?P<version>.*)$/';

    static $composerDefinition;

    static $composerRequires;

    /**
     * @param Event $event
     */
    public static function install(Event $event)
    {
        $io = $event->getIO();
        $composer = $event->getComposer();

        // Get composer.json
        $composerFile = Factory::getComposerFile();
        $json = new JsonFile($composerFile);
        self::$composerDefinition = $json->read();

        $io->write('<info>Setting up optional packages</info>');

        // Get root package
        $rootPackage = $composer->getPackage();
        while ($rootPackage instanceof AliasPackage) {
            $rootPackage = $rootPackage->getAliasOf();
        }

        // Get required packages
        self::$composerRequires = $rootPackage->getRequires();

        $config = require __DIR__ . '/config.php';

        foreach ($config['questions'] as $questionName => $question) {
            $defaultOption = 1;
            if (isset(self::$composerDefinition['extra']['optional-packages'][$questionName])) {
                // Skip question, it's already answered
                continue;
            }

            // Get answer
            $answer = self::askQuestion($composer, $io, $question, $defaultOption);

            // Save user selected option
            self::$composerDefinition['extra']['optional-packages'][$questionName] = $answer;

            if (is_numeric($answer)) {
                // Add packages to install
                foreach ($question['options'][$answer]['packages'] as $packageName) {
                    self::addPackage($io, $packageName, $config['packages'][$packageName]);
                }
            } elseif ($question['custom-package'] === true && preg_match(self::PACKAGE_REGEX, $answer, $match)) {
                self::addPackage($io, $match['name'], $match['version']);
                if (isset($question['custom-package-warning'])) {
                    $io->write(sprintf('  <warning>%s</warning>', $question['custom-package-warning']));
                }
            }

            // Update composer definition
            $json->write(self::$composerDefinition);
        }

        // Set required packages
        $rootPackage->setRequires(self::$composerRequires);
    }

    /**
     * Prepare and ask questions and return the answer
     *
     * @param Composer $composer
     * @param IOInterface $io
     * @param $question
     * @param $defaultOption
     * @return bool|int|string
     */
    private static function askQuestion(Composer $composer, IOInterface $io, $question, $defaultOption)
    {
        // Construct question
        $ask = [
            "\n  <question>" . $question['question'] . "</question>\n"
        ];

        $defaultText = $defaultOption;
        foreach ($question['options'] as $key => $option) {
            if ($key == $defaultOption) {
                $defaultText = $option['name'];
            }
            $ask[] = sprintf("  [<comment>%d</comment>] %s\n", $key, $option['name']);
        }

        if ($question['required'] !== true) {
            $ask[] = "  [<comment>n</comment>] None of the above\n";
        }

        if ($question['custom-package'] === true) {
            $ask[] = sprintf("  Make your selection or type a composer package name and version <comment>(%s)</comment>: ", $defaultText);
        } else {
            $ask[] = sprintf("  Make your selection <comment>(%s)</comment>: ", $defaultText);
        }

        while (true) {
            // Ask for user input
            $answer = $io->ask($ask, $defaultOption);

            // Handle none of the options
            if ($answer == 'n' && $question['required'] !== true) {
                return 'n';
            }

            // Handle numeric options
            if (is_numeric($answer) && isset($question['options'][(int) $answer])) {
                return (int) $answer;
            }

            // Search for package
            if ($question['custom-package'] === true && preg_match(self::PACKAGE_REGEX, $answer, $match)) {
                $packageName = $match['name'];
                $packageVersion = $match['version'];

                if (!$packageVersion) {
                    $io->write('<error>No package version specified</error>');
                    continue;
                }

                $io->write(sprintf('  - Searching for <info>%s:%s</info>', $packageName, $packageVersion));

                $optionalPackage = $composer->getRepositoryManager()->findPackage($packageName, $packageVersion);
                if (!$optionalPackage) {
                    $io->write(sprintf('<error>Package not found %s:%s</error>', $packageName, $packageVersion));
                    continue;
                }

                return sprintf('%s:%s', $packageName, $packageVersion);
            }

            $io->write('<error>Invalid answer</error>');
            continue;
        }

        return false;
    }

    /**
     * Add a package
     *
     * @param IOInterface $io
     * @param $packageName
     * @param $packageVersion
     */
    private static function addPackage(IOInterface $io, $packageName, $packageVersion)
    {
        $io->write(sprintf("  - Adding package <info>%s</info> (<comment>%s</comment>)", $packageName, $packageVersion));
        self::$composerRequires[$packageName] = new Link('__root__', $packageName, null, 'requires', $packageVersion);

        // Save package to composer.json
        self::$composerDefinition['require'][$packageName] = $packageVersion;
    }
}
