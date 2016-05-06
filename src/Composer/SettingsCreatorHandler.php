<?php
/**
 * Created by PhpStorm.
 * User: carlosrevillo
 * Date: 17/04/16
 * Time: 12:23
 */

namespace Crevillo\PlatformLegacyInstallerBundle\Composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class SettingsCreatorHandler
{

    const PARAMETERS_FILE = 'app/config/parameters.yml';

    const PARAMETERS_DIST_FILE = 'app/config/parameters.yml.dist';

    const PARAMETERS_KEY = 'parameters';

    public static function createLegacyConfigFile(Event $event)
    {
        $io = $event->getIO();
        $extras = $event->getComposer()->getPackage()->getExtra();
        $ezpublish_legacy_dir = $extras['ezpublish-legacy-dir'];

        $io->write(sprintf('<info>Creating config.php file</info>'));

        $configFileContents = "<?php\n";
        $configFileContents .= "// This file is created during composer install.\n";
        $configFileContents .= "define( 'EZP_APP_FOLDER_NAME', 'app' );\n";

        $fs = new Filesystem();
        $fs->dumpFile(
            $ezpublish_legacy_dir . '/config.php',
            $configFileContents
        );
    }

    /**
     * @param Event $event
     */
    public static function createLegacySettingsFiles(Event $event)
    {
        $parameters = self::getParametersValues();
        $settingsFiles = self::getFilesToWrite();

        $settingsWriter = new LegacySettingsWriter($event, $parameters['parameters']);
        $settingsWriter->buildFiles($settingsFiles);
    }

    /**
     * Gets values entered by the user in the install process
     *
     * @return mixed
     */
    private static function getParametersValues()
    {
        $yamlParser = new Parser();

        // Find the expected params
        $expectedValues = $yamlParser->parse(file_get_contents(self::PARAMETERS_DIST_FILE));
        if (!isset($expectedValues[self::PARAMETERS_KEY])) {
            throw new \InvalidArgumentException(sprintf('The top-level key %s is missing.', self::PARAMETERS_KEY));
        }

        // find the actual params
        $actualValues = array_merge(
        // Preserve other top-level keys than `$parameterKey` in the file
            $expectedValues,
            array(self::PARAMETERS_KEY => array())
        );

        $existingValues = $yamlParser->parse(file_get_contents(self::PARAMETERS_FILE));
        if ($existingValues === null) {
            $existingValues = array();
        }
        if (!is_array($existingValues)) {
            throw new \InvalidArgumentException(sprintf('The existing "%s" file does not contain an array', self::PARAMETERS_FILE));
        }

        return array_merge($actualValues, $existingValues);
    }

    /**
     * Reads file to write from the installer.yml file
     *
     * @return mixed
     */
    private static function getFilesToWrite()
    {
        $yamlParser = new Parser();
        $settingsFiles = $yamlParser->parse(
            file_get_contents(__DIR__ . '/../Resources/config/installer.yml')
        );

        return $settingsFiles;
    }

    
}
