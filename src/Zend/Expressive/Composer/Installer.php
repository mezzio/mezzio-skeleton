<?php

namespace Zend\Expressive\Composer;

use Composer\Factory;
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
 *      "pre-update-cmd": "TwentyFirstHall\\PhpbbInstaller\\ScriptHandler::install",
 *      "pre-install-cmd": "TwentyFirstHall\\PhpbbInstaller\\ScriptHandler::install"
 *  },
 *
 * Test this script by running ``composer run-script pre-install-cmd`` or ``composer update``.
 *
 * @package Zend\Expressive\Composer
 */
class Installer
{
    /**
     * @param Event $event
     */
    public static function setup(Event $event)
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        //$packages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();
        //$installationManager = $composer->getInstallationManager();

        // Get composer.json
        $composerFile = Factory::getComposerFile();
        $json = new JsonFile($composerFile);
        $composerDefinition = $json->read();
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
        $requires = $rootPackage->getRequires();

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

            // @TODO:  Use while (true) loop until a valid option is given.
            while (true) {
                // Ask for user input
                $answer = $io->ask($ask, $defaultOption);

                // Handle none of the options
                if ('n' == $answer) {
                    $io->write('  <info>Install none of the given packages</info>');
                    $answer = false;
                    break;
                }

                // Search for package
                if (!is_numeric($answer)) {
                    if (!preg_match('/^(?P<name>[^:]+\/[^:]+)([:]*)(?P<version>.*)$/', $answer, $match)) {
                        $io->write('<error>Invalid package required</error>');
                        continue;
                    }

                    $packageName = $match['name'];
                    $packageVersion = $match['version'];

                    if (!$packageVersion) {
                        $io->write('<error>No package version specified</error>');
                        continue;
                    }

                    $io->write(sprintf('  <info>Searching for package %s:%s</info>', $packageName, $packageVersion));

                    $optionalPackage = $composer->getRepositoryManager()->findPackage($packageName, $packageVersion);
                    if (!$optionalPackage) {
                        $io->write(sprintf('  <error>Package not found %s:%s</error>', $packageName, $packageVersion));
                        continue;
                    }

                    // Add package
                    $io->write(sprintf(
                        "  - Adding package <info>%s</info> (<comment>%s</comment>)",
                        $optionalPackage->getName(),
                        $optionalPackage->getPrettyVersion()
                    ));
                    $composerDefinition['require'][$packageName] = $packageVersion;

                    break;
                }

                // Fallback to default
                if (!isset($question['options'][(int) $answer])) {
                    $io->write('<error>Invalid answer, falling back to option 1</error>');
                    $answer = 1;
                }

                break;
            }

            if (!is_numeric($answer)) {
                continue;
            }

            // Save user selected option
            $userSelections->$questionName = $answer;

            // Add packages to install
            foreach ($question['options'][$answer]['packages'] as $packageName) {
                $packageVersion = $config['packages'][$packageName];
                $io->write(sprintf("  - Adding package <info>%s</info> (<comment>%s</comment>)", $packageName, $packageVersion));
                $requires[$packageName] = new Link('__root__', $packageName, null, 'requires', $packageVersion);

                // Save package to composer.json
                $composerDefinition['require'][$packageName] = $packageVersion;
                $json->write($composerDefinition);
            }
        }

        // Set required packages
        $rootPackage->setRequires($requires);

        // Save user selected options
        file_put_contents($userSelectionsFile, json_encode($userSelections, JSON_PRETTY_PRINT));

        $io->write("\n<info>Finished setting up optional packages</info>");
    }
}
