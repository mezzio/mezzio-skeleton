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

        $io->write(sprintf('<info>Set up Zend Expressive installer</info>'));

        // Get root package
        $rootPackage = $composer->getPackage();
        while ($rootPackage instanceof AliasPackage) {
            $rootPackage = $rootPackage->getAliasOf();
        }

        // Get required packages
        $requires = $rootPackage->getRequires();

        $config = require __DIR__ . '/config.php';

        foreach ($config['questions'] as $question) {
            // Construct question
            $ask = [
                "\n" . $question['question'] . "\n"
            ];

            foreach ($question['options'] as $key => $option) {
                $default = ($key == 1) ? ' (default)' : '';
                $ask[] = sprintf(" [%d] %s%s\n", $key, $option['name'], $default);
            }
            $ask[] = ': ';

            // Ask for user input
            $answer = $io->ask($ask, 1);

            // Fallback to default
            if (!isset($question['options'][$answer])) {
                $io->write('<error>Invalid answer, falling back to default</error>');
                $answer = 1;
            }

            // Add packages to install
            foreach ($question['options'][$answer]['packages'] as $packageName) {
                $packageVersion = $config['packages'][$packageName];
                $io->write(sprintf("<info>Adding package '%s':'%s'</info>", $packageName, $packageVersion));
                $requires[$packageName] = new Link('__root__', $packageName, null, 'requires', $packageVersion);
            }
        }

        // Set required packages
        $rootPackage->setRequires($requires);

        if ($io->isVerbose()) {
            $io->write('<info>Job\'s done!</info>');
        }
    }
}
