<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Service;

use Sitegeist\FluidComponentsLinter\CodeQuality\Check\CheckInterface;
use Sitegeist\FluidComponentsLinter\CodeQuality\Component;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\Issue;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;
use Sitegeist\FluidComponentsLinter\Exception\ComponentStructureException;
use Sitegeist\FluidComponentsLinter\Fluid\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\View\TemplateView;

class CodeQualityService
{
    protected ?TemplateView $view = null;

    public function __construct(
        protected array $configuration,
        protected array $checks, // @var string[]
    ) {
        $this->initializeChecks($checks);
        $this->initializeView();
    }

    protected function initializeChecks(array $checks): void
    {
        foreach ($checks as $checkClassName) {
            if (!is_subclass_of($checkClassName, CheckInterface::class)) {
                throw new \Exception(sprintf(
                    'Invalid code quality check class: %s',
                    $checkClassName
                ), 1595870407);
            }
        }
        $this->checks = $checks;
    }

    protected function initializeView(): void
    {
        $this->view = new TemplateView();
        $viewHelperResolver = new ViewHelperResolver();
        $viewHelperResolver->setNamespaces([
            'fc' => ['Sitegeist\\FluidComponentsLinter\\ViewHelpers']
        ]);
        $this->view->getRenderingContext()->setViewHelperResolver($viewHelperResolver);
    }

    public function validateComponent(string $path): array
    {
        // Parse component file and report eventual syntax errors
        try {
            $parsedTemplate = $this->view->getRenderingContext()->getTemplateParser()->parse(
                file_get_contents($path),
                $path
            );
        } catch (Exception $e) {
            preg_match('#in template .+, line ([0-9]+) at character ([0-9]+).#', $e->getMessage(), $matches);
            $issue = $this->blocker($e->getMessage(), $path, (int) $matches[1], (int) $matches[2]);
            return [$issue];
        }

        // Validate and extract basic component structure
        try {
            $component = new Component(
                $path,
                $parsedTemplate->getRootNode(),
                $this->configuration['component']['requireStrictComponentStructure']['check']
            );
        } catch (ComponentStructureException $e) {
            $issue = $this->blocker($e->getMessage(), $path);
            return [$issue];
        }

        $issues = [];
        foreach ($this->checks as $checkClassName) {
            $checkObject = new $checkClassName($component, $this->configuration);
            $issues = array_merge(
                $issues,
                $checkObject->check()
            );
        }

        return $issues;
    }

    protected function blocker(string $message, string $path, ?int $line = null, ?int $column = null): Issue
    {
        $issue = new Issue($message, [], $path, $line, $column);
        return $issue->setSeverity(IssueInterface::SEVERITY_BLOCKER);
    }
}
