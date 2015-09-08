<?php

namespace App\Composer;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\AliasPackage;
use Composer\Package\Link;
use Composer\Package\Version\VersionParser;
use Composer\Script\Event;
use Composer\Package\BasePackage;

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
 * @author Geert Eltink <https://xtreamwayz.github.io/>
 */
class OptionalPackages
{
    const PACKAGE_REGEX = '/^(?P<name>[^:]+\/[^:]+)([:]*)(?P<version>.*)$/';

    private static $config;

    private static $composerDefinition;

    private static $composerRequires;

    private static $composerDevRequires;

    private static $stabilityFlags;

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

        $projectRoot = realpath(dirname($composerFile));

        $io->write("<info>Setting up optional packages</info>");

        // Get root package
        $rootPackage = $composer->getPackage();
        while ($rootPackage instanceof AliasPackage) {
            $rootPackage = $rootPackage->getAliasOf();
        }

        // Get required packages
        self::$composerRequires = $rootPackage->getRequires();
        self::$composerDevRequires = $rootPackage->getDevRequires();

        // Get stability flags
        self::$stabilityFlags = $rootPackage->getStabilityFlags();

        self::$config = require __DIR__ . '/config.php';

        foreach (self::$config['questions'] as $questionName => $question) {
            $defaultOption = (isset($question['default'])) ? $question['default'] : 1;
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
                if (isset($question['options'][$answer]['packages'])) {
                    foreach ($question['options'][$answer]['packages'] as $packageName) {
                        self::addPackage($io, $packageName, self::$config['packages'][$packageName]);
                    }
                }
                // Copy files
                if (isset($question['options'][$answer]['copy-files'])) {
                    foreach ($question['options'][$answer]['copy-files'] as $source => $target) {
                        self::copyFile($io, $projectRoot, $source, $target);
                    }
                }
            } elseif ($question['custom-package'] === true && preg_match(self::PACKAGE_REGEX, $answer, $match)) {
                self::addPackage($io, $match['name'], $match['version']);
                if (isset($question['custom-package-warning'])) {
                    $io->write(sprintf("  <warning>%s</warning>", $question['custom-package-warning']));
                }
            }

            // Update composer definition
            $json->write(self::$composerDefinition);
        }

        // Set required packages
        $rootPackage->setRequires(self::$composerRequires);
        $rootPackage->setDevRequires(self::$composerDevRequires);

        // Set stability flags
        $rootPackage->setStabilityFlags(self::$stabilityFlags);

        // House keeping
        $io->write("<info>Remove installer</info>");
        unset(self::$composerDefinition['extra']['optional-packages']);
        if (empty(self::$composerDefinition['extra'])) {
            unset(self::$composerDefinition['extra']);
        }

        unset(self::$composerDefinition['scripts']['pre-update-cmd']);
        unset(self::$composerDefinition['scripts']['pre-install-cmd']);
        if (empty(self::$composerDefinition['scripts'])) {
            unset(self::$composerDefinition['scripts']);
        }

        // Update composer definition
        $json->write(self::$composerDefinition);

        // @TODO: Remove installer - Not working, getting unlink(D:\projects\test/src/Zend): Permission denied
        //array_map('unlink', glob($projectRoot . '/src/Zend'));
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
            sprintf("\n  <question>%s</question>\n", $question['question']),
        ];

        $defaultText = $defaultOption;

        foreach ($question['options'] as $key => $option) {
            $defaultText = ($key === $defaultOption) ? $option['name'] : $defaultText;
            $ask[]       = sprintf("  [<comment>%d</comment>] %s\n", $key, $option['name']);
        }

        if ($question['required'] !== true) {
            $ask[] = "  [<comment>n</comment>] None of the above\n";
        }

        $ask[] = ($question['custom-package'] === true)
            ? sprintf(
                "  Make your selection or type a composer package name and version <comment>(%s)</comment>: ",
                $defaultText
            )
            : sprintf("  Make your selection <comment>(%s)</comment>: ", $defaultText);

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
                $packageName    = $match['name'];
                $packageVersion = $match['version'];

                if (! $packageVersion) {
                    $io->write("<error>No package version specified</error>");
                    continue;
                }

                $io->write(sprintf("  - Searching for <info>%s:%s</info>", $packageName, $packageVersion));

                $optionalPackage = $composer->getRepositoryManager()->findPackage($packageName, $packageVersion);
                if (! $optionalPackage) {
                    $io->write(sprintf("<error>Package not found %s:%s</error>", $packageName, $packageVersion));
                    continue;
                }

                return sprintf('%s:%s', $packageName, $packageVersion);
            }

            $io->write("<error>Invalid answer</error>");
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
        $io->write(sprintf(
            "  - Adding package <info>%s</info> (<comment>%s</comment>)",
            $packageName,
            $packageVersion
        ));

        // Create package link
        $link = new Link('__root__', $packageName, null, 'requires', $packageVersion);

        // Add package to the root package and composer.json requirements
        if (in_array($packageName, self::$config['require-dev'])) {
            unset(self::$composerDefinition['require'][$packageName]);
            unset(self::$composerRequires[$packageName]);

            self::$composerDefinition['require-dev'][$packageName] = $packageVersion;
            self::$composerDevRequires[$packageName] = $link;
        } else {
            unset(self::$composerDefinition['require-dev'][$packageName]);
            unset(self::$composerDevRequires[$packageName]);

            self::$composerDefinition['require'][$packageName] = $packageVersion;
            self::$composerRequires[$packageName] = $link;
        }

        // Set package stability if needed
        switch (VersionParser::parseStability($packageVersion)) {
            case 'dev':
                self::$stabilityFlags[$packageName] = BasePackage::STABILITY_DEV;
                break;
            case 'alpha':
                self::$stabilityFlags[$packageName] = BasePackage::STABILITY_ALPHA;
                break;
            case 'beta':
                self::$stabilityFlags[$packageName] = BasePackage::STABILITY_BETA;
                break;
            case 'RC':
                self::$stabilityFlags[$packageName] = BasePackage::STABILITY_RC;
                break;
        }
    }

    private static function copyFile(IOInterface $io, $projectRoot, $source, $target, $force = false)
    {
        // Copy file
        if ($force === false && is_file($projectRoot . $target)) {
            return;
        }

        $destinationPath = dirname($projectRoot . $target);
        if (! is_dir($destinationPath)) {
            mkdir($destinationPath, 0775, true);
        }

        $io->write(sprintf("  - Copying <info>%s</info>", $target));
        copy(__DIR__ . $source, $projectRoot . $target);
    }
}
