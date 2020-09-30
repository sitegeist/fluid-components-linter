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
use Sitegeist\FluidComponentsLinter\Service\ConfigurationService;
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
                'Component files that should be included'
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
                'Name of configuration preset',
                false
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Path to custom configuration file',
                false
            )
            ->addOption(
                'severity',
                null,
                InputOption::VALUE_OPTIONAL,
                'Minimum severity, all issues below this severity will be skipped',
                IssueInterface::SEVERITY_INFO
            )
            ->addOption(
                'ignore',
                'i',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Glob patterns that define which files should be skipped',
                []
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
            $configurationService = new ConfigurationService;
            $configuration = $configurationService->getFinalConfiguration(
                $input->getOption('preset'),
                $input->getOption('config')
            );
            $registeredChecks = $configurationService->getRegisteredChecks();

            $componentService = new ComponentService;
            $components = $componentService->findComponentsInPaths(
                $input->getArgument('paths'),
                $input->getOption('extension')
            );

            $ignorePatterns = array_merge(
                $configuration['files']['ignorePatterns'],
                $input->getOption('ignore')
            );
            $components = $componentService->removeComponentsFromIgnoreList(
                $components,
                $ignorePatterns
            );

            $codeQualityService = new CodeQualityService($configuration, $registeredChecks);
            $issues = [];
            foreach ($components as $componentPath) {
                $issues = array_merge(
                    $issues,
                    $codeQualityService->validateComponent($componentPath)
                );
            }

            $skipSeverities = $this->determineSeveritiesToSkip($input->getOption('severity'));
            if (!empty($skipSeverities)) {
                $issues = array_filter($issues, function (IssueInterface $issue) use ($skipSeverities) {
                    return !in_array($issue->getSeverity(), $skipSeverities);
                });
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
            if (in_array($issue->getSeverity(), $this->fatalSeverities)) {
                return 1;
            }
        }
        return 0;
    }

    protected function determineSeveritiesToSkip(string $minSeverity)
    {
        $skipSeverities = [];
        foreach (IssueInterface::SEVERITIES as $severity) {
            if ($minSeverity === $severity) {
                break;
            }
            $skipSeverities[] = $severity;
        }
        return $skipSeverities;
    }
}
