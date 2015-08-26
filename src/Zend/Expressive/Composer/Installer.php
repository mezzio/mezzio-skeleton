<?php

namespace Zend\Expressive\Composer;

use Composer\Package\AliasPackage;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Repository\PackageRepository;
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
 * Test this script by running ``composer run-script pre-install-cmd``.
 *
 * @package Zend\Expressive\Composer
 */
class Installer
{
    /**
     * Composer setup script
     *
     * Run ``composer run-script post-install-cmd`` to test the script.
     *
     * @param Event $event
     */
    public static function setup(Event $event)
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        //$packages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();

        $installationManager = $composer->getInstallationManager();

        $io->write(sprintf('<info>Set up Zend Expressive installer</info>'));

        // Get root package
        $rootPackage = $composer->getPackage();
        while ($rootPackage instanceof AliasPackage) {
            $rootPackage = $rootPackage->getAliasOf();
        }

        // Get required packages
        $requires = $rootPackage->getRequires();

        $routerAnswer = $io->ask([
            'Which router you want to use? ',
            '[1] aura/router ',
            '[2] nikic/fast-route ',
            ': '
        ], 1);

        switch ($routerAnswer) {
            case '2':
                $routerPackage = 'nikic/fast-route';
                $routerVersion = '^0.6.0';
                break;
            case '1':
            default:
                $routerPackage = 'aura/router';
                $routerVersion = '^2.3';
                break;
        }

        $io->write(sprintf('<info>You selected: %s</info>', $routerPackage));
        $requires[$routerPackage] = new Link('__root__', $routerPackage, null, 'requires', $routerVersion);

        // Set required packages
        $rootPackage->setRequires($requires);

        $io->write('<info>Job\'s done!</info>');
    }
}
