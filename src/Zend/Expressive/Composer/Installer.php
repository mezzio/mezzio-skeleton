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
 *      "pre-update-cmd": "Zend\\Expressive\\Composer\\Installer::setup",
 *      "pre-install-cmd": "Zend\\Expressive\\Composer\\Installer::setup"
 *  },
 *
 * @package Zend\Expressive\Composer
 */
class Installer
{
    const PACKAGE_REGEX = '/^(?P<name>[^:]+\/[^:]+)([:]*)(?P<version>.*)$/';

    static $composerDefinition;

    static $composerRequires;

    /**
     * @param Event $event
     */
    public static function setup(Event $event)
    {
        $io = $event->getIO();
        $composer = $event->getComposer();

        // Get composer.json
        $composerFile = Factory::getComposerFile();
        $json = new JsonFile($composerFile);
        self::$composerDefinition = $json->read();
        $composerLockFile = dirname($composerFile) . '/composer.lock';

        // This script only works during update or during install if there is no lock file
        if ($event->getName() == 'pre-install-cmd' && is_file($composerLockFile)) {
            $io->write('<warning>To set up optional packages either delete the composer.lock file or run \'composer update\'</warning>');
            return;
        }

        $io->write('<info>Set up optional packages</info>');

        // Get root package
        $rootPackage = $composer->getPackage();
        while ($rootPackage instanceof AliasPackage) {
            $rootPackage = $rootPackage->getAliasOf();
        }

        // Get required packages
        self::$composerRequires = $rootPackage->getRequires();

        $config = require __DIR__ . '/config.php';

        $userSelectionsFile = __DIR__ . '/data.json';
        $userSelections = new \stdClass();
        if (is_file($userSelectionsFile)) {
            $userSelections = json_decode(file_get_contents($userSelectionsFile));
        }

        foreach ($config['questions'] as $questionName => $question) {
            $defaultOption = 1;
            if (isset($userSelections->$questionName)
                && isset($question['options'][$userSelections->$questionName])
            ) {
                $defaultOption = $userSelections->$questionName;
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
            } elseif (preg_match(self::PACKAGE_REGEX, $answer, $match)) {
                self::addPackage($io, $match['name'], $match['version']);
            }

            // Update composer definition
            $json->write(self::$composerDefinition);
        }

        // Set required packages
        $rootPackage->setRequires(self::$composerRequires);

        // Save user selected options
        file_put_contents($userSelectionsFile, json_encode($userSelections, JSON_PRETTY_PRINT));

        $io->write("\n<info>Finished setting up optional packages</info>");
    }

    private static function askQuestion(Composer $composer, IOInterface $io, $question, $defaultOption)
    {
        // Construct question
        $ask = [
            "\n<question>" . $question['question'] . "</question>\n"
        ];

        foreach ($question['options'] as $key => $option) {
            $default = ($key == $defaultOption) ? ' <comment>(default)</comment>' : '';
            $ask[] = sprintf("  [<comment>%d</comment>] %s%s\n", $key, $option['name'], $default);
        }
        $ask[] = "  [<comment>n</comment>] None of the above\n";
        $ask[] = "  <comment>Make your selection or press return to select the default:</comment> ";

        while (true) {
            // Ask for user input
            $answer = $io->ask($ask, $defaultOption);

            // Handle none of the options
            if ($answer == 'n') {
                return 'n';
            }

            // Handle numeric options
            if (is_numeric($answer) && isset($question['options'][(int) $answer])) {
                return (int) $answer;
            }

            // Search for package
            if (preg_match(self::PACKAGE_REGEX, $answer, $match)) {
                $packageName = $match['name'];
                $packageVersion = $match['version'];

                if (!$packageVersion) {
                    $io->write('<error>No package version specified</error>');
                    continue;
                }

                $io->write(sprintf('  <info>Searching for package %s:%s</info>', $packageName, $packageVersion));

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

    private static function addPackage(IOInterface $io, $packageName, $packageVersion)
    {
        $io->write(sprintf("  - Adding package <info>%s</info> (<comment>%s</comment>)", $packageName, $packageVersion));
        self::$composerRequires[$packageName] = new Link('__root__', $packageName, null, 'requires', $packageVersion);

        // Save package to composer.json
        self::$composerDefinition['require'][$packageName] = $packageVersion;
    }
}
