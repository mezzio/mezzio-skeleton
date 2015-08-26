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
        $eventName = $event->getName();
        $io = $event->getIO();
        $composer = $event->getComposer();
        $packages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();

        $installationManager = $composer->getInstallationManager();

        $io->write(sprintf('<info>Running Zend\Expressive\Composer\Installer::setup()</info>'));

        $rootPackage = $composer->getPackage();
        while ($rootPackage instanceof AliasPackage) {
            $rootPackage = $rootPackage->getAliasOf();
        }

        $requires = $rootPackage->getRequires();

        $answer = $io->ask([
            'Which router you want to use? ',
            '[1] aura/router ',
            '[2] nikic/fast-route ',
            ': '
        ], 1);
        $io->write(sprintf('<info>You answered: %s</info>', $answer));

        switch ($answer) {
            case '2':
                $requires['nikic/fast-route'] = new Link('__root__', 'nikic/fast-route', null, 'requires', '^0.6.0');
                break;
            case '1':
            default:
                $requires['aura/router'] = new Link('__root__', 'aura/router', null, 'requires', '^2.3');
                break;
        }

        $rootPackage->setRequires($requires);

        $io->write('<info>Job\'s done!</info>');
    }
}
