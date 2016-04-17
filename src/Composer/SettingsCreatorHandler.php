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

class SettingsCreatorHandler
{
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
    public static function createLegacyOverrideFiles(Event $event)
    {
        $processor = new Processor($event->getIO());

        $processor->buildLegacyParametersFiles();
    }
}