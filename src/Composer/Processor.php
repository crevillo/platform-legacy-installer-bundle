<?php

namespace Crevillo\PlatformLegacyInstallerBundle\Composer;

use Composer\IO\IOInterface;
use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class Processor
{
    const PARAMETERS_FILE = 'app/config/parameters.yml';

    const PARAMETERS_DIST_FILE = 'app/config/parameters.yml.dist';

    const PARAMETERS_KEY = 'parameters';

    private $io;

    private $legacyFileProcessor;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
        $this->legacyFileProcessor = new LegacyParametersFilesProcessor($io);
    }

    public function buildLegacyParametersFiles()
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
        $actualValues = array_merge($actualValues, $existingValues);

        $this->legacyFileProcessor->processFiles($actualValues[self::PARAMETERS_KEY]);
    }
}
