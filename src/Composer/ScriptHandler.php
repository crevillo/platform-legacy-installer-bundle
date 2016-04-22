<?php
namespace Crevillo\PlatformLegacyInstallerBundle\Composer;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as DistributionBundleScriptHandler;
use Composer\Script\CommandEvent;
class ScriptHandler extends DistributionBundleScriptHandler
{
    /**
     * Just dump welcome text on how to install eZ Platform with Legacy.
     *
     * @param $event CommandEvent A instance
     */
    public static function installWelcomeText(CommandEvent $event)
    {
        $event->getIO()->write(<<<'EOT'
<fg=cyan>Welcome to eZ Platform with Legacy!</fg=cyan>
<options=bold>Please read the INSTALL.md file to complete the installation.</options>
<options=bold>Assuming that your database information were correctly entered, you may install a clean database by running the install command:</options>
<comment>    $ php app/console --env=prod ezplatform:install legacy_clean</comment>
EOT
        );
    }
}