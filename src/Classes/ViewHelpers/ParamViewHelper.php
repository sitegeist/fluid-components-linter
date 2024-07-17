<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;

class ParamViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Parameter name', true);
        $this->registerArgument('type', 'string', 'Parameter type', true);
        $this->registerArgument('optional', 'bool', 'Is parameter optional?', false, false);
        $this->registerArgument('default', 'string', 'Default value');
        $this->registerArgument('description', 'string', 'Description of the parameter');
    }

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
