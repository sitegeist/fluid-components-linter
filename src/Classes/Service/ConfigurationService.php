<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Service;

use Sitegeist\FluidComponentsLinter\Configuration\LintConfiguration;
use Sitegeist\FluidComponentsLinter\Exception\ConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationService
{
    public function getFinalConfiguration($configurationPreset, $configurationFile): array
    {
        $configurationParts = [
            $this->getPresetConfiguration('default'),
            ($configurationPreset !== false) ? $this->getPresetConfiguration($configurationPreset) : [],
            ($configurationFile !== false)
                ? $this->getCustomConfiguration($configurationFile)
                : $this->getCustomConfiguration('.fclint.json', true)
        ];

        $processor = new Processor();
        return $processor->processConfiguration(
            new LintConfiguration,
            $configurationParts
        );
    }

    public function getPresetConfiguration(string $preset): array
    {
        $path = sprintf(__DIR__ . '/../../Configuration/%s.fclint.json', $preset);
        if (!file_exists($path)) {
            throw new ConfigurationException(sprintf(
                'Invalid configuration preset: %s',
                $preset
            ), 1595789341);
        }

        $configuration = json_decode(file_get_contents($path), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigurationException(sprintf(
                'Invalid configuration preset file: %s (%s)',
                $preset,
                json_last_error_msg()
            ), 1595789342);
        }

        return $configuration;
    }

    public function getCustomConfiguration(string $path, bool $optional = false): ?array
    {
        if ($path === '') {
            return null;
        }

        if (!file_exists($path)) {
            if ($optional) {
                return null;
            }

            throw new \Exception(sprintf(
                'The specified configuration file does not exist: %s',
                $path
            ), 1595921202);
        }

        $configuration = json_decode(file_get_contents($path), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigurationException(sprintf(
                'Invalid configuration file: %s (%s)',
                $path,
                json_last_error_msg()
            ), 1595921203);
        }

        return $configuration;
    }

    public function getRegisteredChecks(): array
    {
        return require(__DIR__ . '/../../Configuration/CodeQualityChecks.php');
    }
}
