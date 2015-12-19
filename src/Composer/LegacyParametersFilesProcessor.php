<?php
/**
 * Created by PhpStorm.
 * User: carlosrevillo
 * Date: 19/12/15
 * Time: 15:55
 */

namespace Crevillo\PlatformLegacyInstallerBundle\Composer;

use Composer\IO\IOInterface;

class LegacyParametersFilesProcessor
{
    const LEGACY_PARAMETERS_FOLDER = 'ezpublish_legacy/settings/override';

    const LEGACY_PARAMETERS_FILES_SUFFIX = '.ini.append.php';

    private $io;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * Proccess all legacy needed files.
     *
     * @param array $values
     */
    public function processFiles(array $values)
    {
        $this->processSiteFile($values);
    }

    /**
     * Writes the file with the contents
     *
     * @param $file
     * @param $contents
     */
    private function dumpSettingsToFile($file, $contents)
    {
        $realFile = self::LEGACY_PARAMETERS_FOLDER . '/' . $file . self::LEGACY_PARAMETERS_FILES_SUFFIX;
        $exists = is_file($realFile);

        $action = $exists ? 'Updating' : 'Creating';
        $this->io->write(sprintf('<info>%s the "%s" file</info>', $action, $realFile));

        if (!is_dir($dir = dirname($realFile))) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($realFile, $contents);
    }

    /**
     * Transforms legacy site related values to ini format and dump them to
     * the file
     *
     * @param array $values
     */
    private function processSiteFile(array $values)
    {
        $databaseSettings = array(
            'DatabaseSettings' => array(
                'DatabaseImplementation' => 'ezmysqli',
                'Server' => $values['database_host'],
                'Port' => $values['database_port'],
                'User' => $values['database_user'],
                'Password' => $values['database_password'],
                'DatabaseName' => $values['database_name']
            )
        );

        $siteFileContents = "<?php /*\n";
        $siteFileContents .= "# This file is auto-generated during the composer install\n";
        $siteFileContents .= $this->transformValuesToIniFormat($databaseSettings);

        $this->dumpSettingsToFile('site', $siteFileContents);
    }

    private function transformValuesToIniFormat($parameters)
    {
        $content = '';

        foreach ($parameters as $group => $settings) {
            $content .= "[" . $group . "]\n";
            foreach ($settings as $key => $value) {
                if (is_array($value)) {
                    for ($i = 0; $i < count($value); $i++) {
                        $content .= $key . "[] = " . $value[$i] . "\n";
                    }
                }
                elseif ($value == "") {
                    $content .= $key . " = \n";
                }
                else $content .= $key . " = " . $value . "\n";
            }
        }

        return $content;
    }
}