<?php
/**
 * Created by PhpStorm.
 * User: carlosrevillo
 * Date: 19/12/15
 * Time: 15:55
 */

namespace Crevillo\PlatformLegacyInstallerBundle\Composer;

use Composer\IO\IOInterface;
use Symfony\Component\Filesystem\Filesystem;

class LegacyParametersFilesProcessor
{
    const LEGACY_PARAMETERS_FOLDER = 'ezpublish_legacy/settings';

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
        $this->createContentStructureMenuFile();
        $this->processSiteAdminFile($values);
        $this->processToolbarAdminFile($values);
        $this->createIconAdminFile($values);
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

        $fs = new Filesystem();
        $dir = dirname($realFile);

        if (!$fs->exists($dir)) {
            $fs->mkdir($dir, 0755);
        }

        $fs->dumpFile($realFile, $contents);
    }

    /**
     * Transforms legacy site related values to ini format and dump them to
     * the file
     *
     * @param array $values
     */
    private function processSiteFile(array $values)
    {
        $settings = array(
            'DatabaseSettings' => array(
                'DatabaseImplementation' => 'ezmysqli',
                'Server' => $values['database_host'],
                'Port' => $values['database_port'],
                'User' => $values['database_user'],
                'Password' => $values['database_password'],
                'DatabaseName' => $values['database_name']
            ),
            'SiteSettings' => array(
                'DefaultAccess' => 'www',
                'SiteList' => array(
                    $values['frontend_siteaccess'],
                    $values['admin_siteaccess']
                )
            ),
            'ExtensionSettings' => array(
                'ActiveExtensions' => array(
                    'ezjscore',
                    'ezflow'
                )
            ),
            'SiteAccessSettings' => array(
                'CheckValidity' => 'false',
                'AvailableSiteAccessList' => array(
                    $values['frontend_siteaccess'],
                    $values['admin_siteaccess']
                ),
                'MatchOrder' => 'uri'
            ),
            'DesignSettings' => array(
                'DesignLocationCache' => 'enabled'
            )
        );

        $siteFileContents = "<?php /*\n";
        $siteFileContents .= "# This file is auto-generated during the composer install\n";
        $siteFileContents .= $this->transformValuesToIniFormat($settings);

        $this->dumpSettingsToFile('override/site', $siteFileContents);
    }

    /**
     * Creates the content structure menu ini file so treemenu is enabled
     * by default
     */
    private function createContentStructureMenuFile()
    {
        $settings = array(
            'TreeMenu' => array(
                'Dynamic' => 'enabled'
            )
        );

        $siteFileContents = "<?php /*\n";
        $siteFileContents .= "# This file is auto-generated during the composer install\n";
        $siteFileContents .= $this->transformValuesToIniFormat($settings);

        $this->dumpSettingsToFile('override/contentstructuremenu', $siteFileContents);
    }

    /**
     * Creates settings/[%admin_siteaccess%]/site.ini.append.php
     *
     * @param array $values
     */
    private function processSiteAdminFile(array $values)
    {
        $settings = array(
            'SiteSettings' => array(
                'DefaultPage' => 'content/dashboard',
                'LoginPage' => 'custom'
            ),
            'SiteAccessSettings' => array(
                'RequireUserLogin' => 'true',
                'RelatedSiteAccessList' => array(
                    $values['frontend_siteaccess'],
                    $values['admin_siteaccess']
                ),
                'ShowHiddenNodes' => 'true'
            ),
            'DesignSettings' => array(
                'SiteDesign' => $values['admin_siteaccess'],
                'AdditionalSiteDesignList' => array('admin')
            ),
            'RegionalSettings' => array(
                'Locale' => 'esl-ES',
                'ContentObjectLocale' => 'esl-ES',
                'ShowUntranslatedObjects' => 'esl-ES',
                'SiteLanguageList' => array('esl-ES'),
                'TextTranslation' => 'enabled'
            ),
            'ContentSettings' => array(
                'CachedViewPreferences' => array(
                    'full' => 'admin_navigation_content=1;admin_children_viewmode=list;admin_list_limit=1'
                )
            )
        );

        $siteFileContents = "<?php /*\n";
        $siteFileContents .= "# This file is auto-generated during the composer install\n";
        $siteFileContents .= $this->transformValuesToIniFormat($settings);

        $this->dumpSettingsToFile('siteaccess/' . $values['admin_siteaccess'] . '/site', $siteFileContents);
    }

    /**
     * Creates settings/[%admin_siteaccess%]/toolbar.ini.append.php
     *
     * @param array $values
     */
    private function processToolbarAdminFile(array $values)
    {
        $settings = array(
            'Toolbar' => array(
                'AvailableToolBarArray' => array(
                    null,
                    'setup',
                    'admin_right',
                    'admin_developer'
                )
            ),
            'Tool' => array(
                'AvailableToolArray' => array(
                    null,
                    'setup_link',
                    'admin_current_user',
                    'admin_bookmarks',
                    'admin_clear_cache',
                    'admin_quick_settings'
                )
            ),
            'Toolbar_setup' => array(
                'Tool' => array(
                    null,
                    'setup_link'
                )
            ),
            'Toolbar_admin_right' => array(
                'Tool' => array(
                    null,
                    'admin_current_user',
                    'admin_preferences',
                    'admin_bookmarks'
                )
            ),
            'Toolbar_admin_developer' => array(
                'Tool' => array(
                    null,
                    'admin_clear_cache',
                    'admin_quick_settings'
                )
            )
        );

        $toolbarFileContents = "<?php /*\n";
        $toolbarFileContents .= "# This file is auto-generated during the composer install\n";
        $toolbarFileContents .= $this->transformValuesToIniFormat($settings);

        $this->dumpSettingsToFile('siteaccess/' . $values['admin_siteaccess'] . '/toolbar', $toolbarFileContents);
    }

    /**
     * Creates settings/%admin_siteaccess%/icon.ini.append.php
     */
    private function createIconAdminFile($values)
    {
        $settings = array(
            'IconSettings' => array(
                'Theme' => 'crystal-admin',
                'Size' => 'normal'
            )
        );

        $iconFileContents = "<?php /*\n";
        $iconFileContents .= "# This file is auto-generated during the composer install\n";
        $iconFileContents .= $this->transformValuesToIniFormat($settings);

        $this->dumpSettingsToFile('siteaccess/' . $values['admin_siteaccess'] . '/icon', $iconFileContents);

    }


    private function transformValuesToIniFormat($parameters)
    {
        $content = '';

        foreach ($parameters as $group => $settings) {
            $content .= "[" . $group . "]\n";
            foreach ($settings as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subkey => $subvalue) {
                        if (is_null($subvalue)) {
                            $content .= $key . "[]\n";
                        }
                        elseif (is_numeric($subkey)) {
                            $content .= $key . "[]=" . $subvalue . "\n";
                        }
                        else {
                            $content .= $key . "[$subkey]=" . $subvalue . "\n";
                        }
                    }
                }
                elseif ($value == "") {
                    $content .= $key . "=\n";
                }
                else $content .= $key . "=" . $value . "\n";
            }
            $content .= "\n";
        }

        return $content;
    }
}