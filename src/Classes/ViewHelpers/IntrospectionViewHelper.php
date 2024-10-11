<?php

declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class IntrospectionViewHelper extends AbstractViewHelper
{
    public $namespaceIdentifier;
    public $methodIdentifier;

    public function setIntrospectionData(string $namespaceIdentifier, string $methodIdentifier): self
    {
        $this->namespaceIdentifier = $namespaceIdentifier;
        $this->methodIdentifier = $methodIdentifier;
        return $this;
    }

    public function getViewhelperTag(): string
    {
        return '<' . $this->getViewhelperTagName() . '>';
    }

    public function getViewhelperTagName(): string
    {
        return sprintf('%s:%s', $this->namespaceIdentifier, $this->methodIdentifier);
    }

    public function validateAdditionalArguments(array $arguments): void
    {
        // Allow all arguments
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
