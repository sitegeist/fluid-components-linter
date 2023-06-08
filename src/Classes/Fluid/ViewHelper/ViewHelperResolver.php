<?php

declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Fluid\ViewHelper;

use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use Sitegeist\FluidComponentsLinter\ViewHelpers\IntrospectionViewHelper;

class ViewHelperResolver extends \TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver
{
    public function createViewHelperInstanceFromClassName($viewHelperClassName)
    {
        $parts = explode('\\', $viewHelperClassName);
        $methodIdentifier = array_pop($parts);
        $namespaceIdentifier = array_pop($parts);

        $instance = new IntrospectionViewHelper;
        $instance->setIntrospectionData($namespaceIdentifier, $methodIdentifier);

        return $instance;
    }

    public function resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier): string
    {
        try {
            return parent::resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier);
        } catch (ParserException $e) {
            // Redirect missing ViewHelpers to introspection placeholder
            return sprintf(
                '%s\\%s\\%s',
                IntrospectionViewHelper::class,
                $namespaceIdentifier,
                $methodIdentifier
            );
        }
    }

    public function isNamespaceValid($namespaceIdentifier): bool
    {
        // Allow all namespaces
        if (!isset($this->namespaces[$namespaceIdentifier])) {
            $this->namespaces[$namespaceIdentifier] = [];
        }
        return true;
    }
}
