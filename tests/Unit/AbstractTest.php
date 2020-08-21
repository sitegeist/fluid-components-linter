<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sitegeist\FluidComponentsLinter\CodeQuality\Component;
use Sitegeist\FluidComponentsLinter\Fluid\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\View\TemplateView;

abstract class AbstractTest extends TestCase
{
    public function createComponent(string $componentSource, bool $strictSyntax = false): Component
    {
        $view = new TemplateView();
        $viewHelperResolver = new ViewHelperResolver();
        $viewHelperResolver->setNamespaces([
            'fc' => ['Sitegeist\\FluidComponentsLinter\\ViewHelpers']
        ]);
        $view->getRenderingContext()->setViewHelperResolver($viewHelperResolver);

        $parsedTemplate = $view->getRenderingContext()->getTemplateParser()->parse(
            $componentSource,
            md5($componentSource)
        );

        $componentPath = __DIR__ . '/TestComponent/TestComponent.html';
        return new Component($componentPath, $parsedTemplate->getRootNode(), $strictSyntax);
    }
}
