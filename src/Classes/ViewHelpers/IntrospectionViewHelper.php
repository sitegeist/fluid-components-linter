<?php

declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;

class IntrospectionViewHelper extends AbstractViewHelper
{
    use ParserRuntimeOnly;

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
        return sprintf('<%s:%s>', $this->namespaceIdentifier, $this->methodIdentifier);
    }

    public function validateAdditionalArguments(array $arguments)
    {
        // Allow all arguments
    }
}
