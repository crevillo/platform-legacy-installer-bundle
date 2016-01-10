<?php

namespace Crevillo\PlatformLegacyInstallerBundle\Composer;

use Composer\Script\Event;

class ScriptHandler
{
    public static function createLegacyOverrideFiles(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();

        if (!isset($extras['legacy-settings-site-parameters'])) {
            throw new \InvalidArgumentException('The parameter handler needs to be configured through the extra.legacy-settings-site-parameters setting.');
        }

        $legacyConfigFiles = $extras['legacy-settings-site-parameters'];

        if (!is_array($legacyConfigFiles)) {
            throw new \InvalidArgumentException('The extra.legacy-settings-site-parameters setting must be an array.');
        }

        $processor = new Processor($event->getIO());

        $processor->buildLegacyParametersFiles();
    }
}
