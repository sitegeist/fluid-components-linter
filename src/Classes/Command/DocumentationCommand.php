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
use TYPO3Fluid\Fluid\View\TemplateView;

class DocumentationCommand extends Command
{
    protected static $defaultName = 'documentation';

    protected function configure()
    {
        $this
            ->setDescription('Validates fluid components based on a specified ruleset')
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

            $view = new TemplateView();
            $view->getTemplatePaths()->setTemplatePathAndFilename(
                __DIR__ . '/../../Resources/Documentation.template.md'
            );
            $view->assign('configuration', $configuration);
            $output->write($view->render());
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                '<error>fluid-components-linter: %s (in %s on line %d)</error>',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            return 1;
        }

        return 0;
    }
}
