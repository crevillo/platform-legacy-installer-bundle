<?php

namespace Crevillo\PlatformLegacyInstallerBundle\Composer;

use Symfony\Component\Filesystem\Filesystem;
use Composer\Script\Event;
use Symfony\Component\Yaml\Parser;

class LegacySettingsWriter
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var
     */
    private $io;

    /**
     * @var Parser
     */
    private $yamlParser;

    /**
     * @var array
     */
    private $values;

    public function __construct(
        Event $event,
        array $values
    )
    {
        $this->fs = new Filesystem();
        $this->io = $event->getIO();
        $this->yamlParser = new Parser();
        $this->values = $values;
    }

    /**
     * Loop over settings and create matched files
     *
     * @param $files
     */
    public function buildFiles($files)
    {
        foreach ($files['legacy_settings_files'] as $key => $yamlFiles) {
            if ($key === 'override') {
                $this->processOverrideYamlFiles($yamlFiles);
            }

            if ($key === 'siteaccess') {
                foreach ($files['legacy_settings_files']['siteaccess'] as $siteAccess => $siteAccessYamlFiles) {
                    if ($siteAccess === '%admin_siteaccess%') {
                        $this->processAdminSiteAccessYamlFiles($siteAccessYamlFiles);
                    }
                }
            }
        }
    }

    /**
     * Process all override files
     *
     * @param $yamlFiles
     */
    private function processOverrideYamlFiles($yamlFiles)
    {
        if (!$this->fs->exists('ezpublish_legacy/settings/override')) {
            $this->io->write(
                sprintf('<info>Creating the ezpublish_legacy/settings/override directory</info>')
            );
            $this->fs->mkdir('ezpublish_legacy/settings/override');
        }

        foreach ($yamlFiles as $key => $file) {
            $data = $this->transformYamlValuesToArray('override/' . $file);

            $file = 'ezpublish_legacy/settings/override/' . $file . '.ini.append.php';
            $fileExists = $this->fs->exists($file);
            $this->io->write(
                sprintf(
                    '<info>%s the "%s" file</info>',
                    $fileExists ? 'Updating' : 'Creating',
                    $file
                )
            );

            $this->fs->dumpFile(
                $file,
                $this->buildContentsForData($data)
            );
        }
    }

    private function processAdminSiteAccessYamlFiles($yamlFiles)
    {
        $admin_siteaccess = $this->values['admin_siteaccess'];

        if (!$this->fs->exists('ezpublish_legacy/settings/siteacces/' . $admin_siteaccess)) {
            $this->io->write(
                sprintf('<info>Creating the ezpublish_legacy/settings/siteacess/%s directory</info>', $admin_siteaccess)
            );
            $this->fs->mkdir('ezpublish_legacy/settings/siteaccess/' . $admin_siteaccess);
        }

        foreach ($yamlFiles as $key => $file) {
            $data = $this->transformYamlValuesToArray('siteaccess/admin_siteaccess/' . $file);

            $file = 'ezpublish_legacy/settings/siteaccess/' . $admin_siteaccess . '/' . $file . '.ini.append.php';
            $fileExists = $this->fs->exists($file);
            $this->io->write(
                sprintf(
                    '<info>%s the "%s" file</info>',
                    $fileExists ? 'Updating' : 'Creating',
                    $file
                )
            );

            $this->fs->dumpFile(
                $file,
                $this->buildContentsForData($data)
            );
        }
    }

    /**
     * Generates the content to be dumped to file
     *
     * @param array $data
     * @return string
     */
    private function buildContentsForData(array $data)
    {
        $contents = "<?php /*\n";
        $contents .= "# This file is auto-generated during the composer install\n";
        $contents .= $this->transformValuesToIniFormat($data);

        return $contents;

    }

    /**
     * Reads the values in the yaml and tranforms to an array
     * replacing parameters in yaml for the values in ezplatform.yml
     *
     * @param string $file
     * @return array
     */
    private function transformYamlValuesToArray($file)
    {
        $settings = $this->yamlParser->parse(
            file_get_contents(__DIR__ . '/../Resources/config/settings/' . $file . '.yml')
        );

        array_walk_recursive($settings, array($this, 'convertParamsToValues'));

        return $settings;
    }

    /**
     * Replaces recursively params with provided values
     *
     * @param $item
     */
    private function convertParamsToValues(&$item)
    {
        $values = $this->values;
        $item = str_replace(
            array(
                '%database_host%',
                '%database_port%',
                '%database_user%',
                '%database_password%',
                '%database_name%',
                '%frontend_siteaccess%',
                '%admin_siteaccess%'
            ),
            array(
                $values['database_host'],
                $values['database_port'],
                $values['database_user'],
                $values['database_password'],
                $values['database_name'],
                $values['frontend_siteaccess'],
                $values['admin_siteaccess']
            ),
            $item
        );
    }

    /**
     * Transform a php array in a string with old legacy init format
     *
     * @param $parametersArray
     * @return string
     */
    private function transformValuesToIniFormat($parametersArray)
    {
        $content = '';
        foreach ($parametersArray as $group => $settings) {
            $content .= "[" . $group . "]\n";
            foreach ($settings as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subkey => $subvalue) {
                        if (is_null($subvalue)) {
                            $content .= $key . "[]\n";
                        } elseif (is_numeric($subkey)) {
                            $content .= $key . "[]=" . $subvalue . "\n";
                        } else {
                            $content .= $key . "[$subkey]=" . $subvalue . "\n";
                        }
                    }
                } elseif ($value == "") {
                    $content .= $key . "=\n";
                } else $content .= $key . "=" . $value . "\n";
            }
            $content .= "\n";
        }

        return $content;
    }
}
