<?php

namespace Zend\Expressive\Composer;

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

        // This script only works during update or during install if there is no lock file
        if ($event->getName() == 'pre-install-cmd') {
            $io->write('<warning>To set up Zend Expressive either delete the composer.lock file or run \'composer update\'</warning>');
            return;
        }

        $io->write('<info>Set up Zend Expressive installer</info>');

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
                "\n" . $question['question'] . "\n"
            ];

            foreach ($question['options'] as $key => $option) {
                $default = ($key == $defaultOption) ? ' (default)' : '';
                $ask[] = sprintf(" [%d] %s%s\n", $key, $option['name'], $default);
            }
            $ask[] = ': ';

            // Ask for user input
            $answer = $io->ask($ask, $defaultOption);

            // Fallback to default
            if (!isset($question['options'][$answer])) {
                $io->write('<error>Invalid answer, falling back to option 1</error>');
                $answer = 1;
            }

            // Save user selected option
            $userSelections->$questionName = $answer;

            // Add packages to install
            foreach ($question['options'][$answer]['packages'] as $packageName) {
                $packageVersion = $config['packages'][$packageName];
                $io->write(sprintf("<info>Adding package '%s':'%s'</info>", $packageName, $packageVersion));
                $requires[$packageName] = new Link('__root__', $packageName, null, 'requires', $packageVersion);
            }
        }

        // Set required packages
        $rootPackage->setRequires($requires);

        // Save user selected options
        file_put_contents($userSelectionsFile, json_encode($userSelections));

        if ($io->isVerbose()) {
            $io->write('<info>Job\'s done!</info>');
        }
    }
}
