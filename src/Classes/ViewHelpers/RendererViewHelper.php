<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RendererViewHelper extends AbstractViewHelper
{
    /**
     * ViewHelper only has functionality during parsing
     */
    public function render()
    {
        return null;
    }

    /**
     * ViewHelper only has functionality during parsing
     */
    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler)
    {
        return '';
    }
}
