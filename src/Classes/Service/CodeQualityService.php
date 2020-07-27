<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Service;

use Sitegeist\FluidComponentsLinter\CodeQuality\Check\CheckInterface;
use Sitegeist\FluidComponentsLinter\CodeQuality\Component;
use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;
use Sitegeist\FluidComponentsLinter\Exception\ComponentStructureException;
use Sitegeist\FluidComponentsLinter\Fluid\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\View\TemplateView;

class CodeQualityService
{
    /** @var array */
    protected $configuration;

    /** @var string[] */
    protected $checks;

    /** @var TemplateView */
    protected $view;

    public function __construct(array $configuration, array $checks)
    {
        $this->configuration = $configuration;
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
        } catch (\TYPO3Fluid\Fluid\Core\Parser\Exception $e) {
            return [$e];
        }

        // Validate and extract basic component structure
        try {
            $component = new Component(
                $path,
                $parsedTemplate->getRootNode(),
                $this->configuration['component']['requireFluidInsideRenderer']
            );
        } catch (ComponentStructureException $e) {
            return [$e];
        }

        $results = [];
        foreach ($this->checks as $checkClassName) {
            $check = new $checkClassName($component, $this->configuration);
            try {
                $checkResults = $check->check();
                if (is_array($checkResults)) {
                    $results = array_merge($results, $checkResults);
                }
            } catch (CodeQualityException $e) {
                $results[] = $e;
            }
        }

        return $results;
    }


}
