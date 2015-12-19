<?php

namespace Crevillo\PlatformLegacyInstallerBundle\Composer;

use Composer\Script\Event;

class ScriptHandler
{
    public static function buildLegacyParameters(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();

        if (!isset($extras['ezpublish-legacy-parameters-files'])) {
            throw new \InvalidArgumentException('The parameter handler needs to be configured through the extra.ezpublish-legacy-parameters-files setting.');
        }

        $legacyConfigFiles = $extras['ezpublish-legacy-parameters-files'];

        if (!is_array($legacyConfigFiles)) {
            throw new \InvalidArgumentException('The extra.ezpublish-legacy-parameters-files setting must be an array.');
        }

        if (array_keys($legacyConfigFiles) !== range(0, count($legacyConfigFiles) - 1)) {
            $legacyConfigFiles = array($legacyConfigFiles);
        }

        $processor = new Processor($event->getIO());

        $processor->buildLegacyParametersFiles();
    }
}
