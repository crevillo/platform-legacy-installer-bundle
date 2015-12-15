<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Crevillo\PlatformLegacyInstallerBundle\Installer;

use EzSystems\PlatformInstallerBundle\Installer\CleanInstaller;
use EzSystems\PlatformInstallerBundle\Installer\Installer;

class LegacyCleanInstaller extends CleanInstaller implements Installer
{
    public function importData()
    {
        parent::importData();
        $this->runQueriesFromFile(
            'vendor/crevillo/ezplatform-legacy-installer-bundle/data/legacy-cleandata.sql'
        );
    }
}
