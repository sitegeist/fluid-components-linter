<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Command;

use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;
use Sitegeist\FluidComponentsLinter\CodeQuality\Output\GroupedOutput;
use Sitegeist\FluidComponentsLinter\CodeQuality\Output\JsonOutput;
use Sitegeist\FluidComponentsLinter\Configuration\LintConfiguration;
use Sitegeist\FluidComponentsLinter\Exception\ConfigurationException;
use Sitegeist\FluidComponentsLinter\Service\CodeQualityService;
use Sitegeist\FluidComponentsLinter\Service\ComponentService;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LintCommand extends Command
{
    protected static $defaultName = 'lint';

    /**
     * Define severities which will lead to an exit status 0
     *
     * @var array
     */
    protected $fatalSeverities = [
        IssueInterface::SEVERITY_BLOCKER,
        IssueInterface::SEVERITY_CRITICAL,
        IssueInterface::SEVERITY_MAJOR
    ];

    protected function configure()
    {
        $this
            ->setDescription('Validates fluid components based on a specified ruleset')
            ->addArgument(
                'paths',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Component namespaces that should be included'
            )
            ->addOption(
                'extension',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Component file extension',
                '.html'
            )
            ->addOption(
                'preset',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Name of configuration preset'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Path to custom configuration file'
            )
            ->addOption(
                'json',
                null,
                InputOption::VALUE_NONE,
                'Output results as json (compatible to codeclimate spec)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $configuration = $this->getFinalConfiguration(
                $input->getOption('preset'),
                $input->getOption('config')
            );

            $componentService = new ComponentService;
            $components = $componentService->findComponentsInPaths(
                $input->getArgument('paths'),
                $input->getOption('extension')
            );

            $codeQualityService = new CodeQualityService($configuration, $this->getRegisteredChecks());
            $issues = [];
            foreach ($components as $componentPath) {
                $issues = array_merge(
                    $issues,
                    $codeQualityService->validateComponent($componentPath)
                );
            }

            if ($input->getOption('json')) {
                JsonOutput::output($output, $issues);
            } else {
                GroupedOutput::output($output, $issues);
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                '<error>fluid-components-linter: %s (in %s on line %d)</error>',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            return 1;
        }

        return $this->determineExitStatus($issues);
    }

    protected function determineExitStatus(array $issues): int
    {
        foreach ($issues as $issue) {
            if (in_array($issue->getSeverity, $this->fatalSeverities)) {
                return 1;
            }
        }
        return 0;
    }

    protected function getFinalConfiguration(?string $configurationPreset, ?string $configurationFile): array
    {
        $configurationParts = [
            $this->getPresetConfiguration('default'),
            (isset($configurationPreset)) ? $this->getPresetConfiguration($configurationPreset) : [],
            (isset($configurationFile)) ? $this->getCustomConfiguration($configurationFile) : []
        ];

        $processor = new Processor();
        return $processor->processConfiguration(
            new LintConfiguration,
            $configurationParts
        );
    }

    protected function getPresetConfiguration(string $preset): array
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

    protected function getCustomConfiguration(string $path): ?array
    {
        if ($path === '') {
            return null;
        }

        if (!file_exists($path)) {
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

    protected function getRegisteredChecks(): array
    {
        return require(__DIR__ . '/../../Configuration/CodeQualityChecks.php');
    }
}
