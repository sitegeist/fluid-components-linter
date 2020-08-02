<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\ViewHelpers\IntrospectionViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class ContentVariableCheck extends AbstractCheck
{
    public function check(): array
    {
        if (!$this->configuration['renderer']['requireRawContent']['check']) {
            return [];
        }
        $this->setDefaultSeverity($this->configuration['renderer']['requireRawContent']['severity']);

        $issues = [];
        if (!$this->checkContentVariableContext($this->component->rootNode)) {
            $issues[] = $this->issue('Variable {content} is not properly wrapped with <f:format.raw>');
        }

        return $issues;
    }

    public function checkContentVariableContext(NodeInterface $node, array $parents = []): bool
    {
        $node = $this->fluidService->resolveEscapingNode($node);

        $lastParent = count($parents) - 1;
        foreach ($node->getChildNodes() as $childNode) {
            $childNode = $this->fluidService->resolveEscapingNode($childNode);

            // Check all parent elements of content variable
            if ($childNode instanceof ObjectAccessorNode && $childNode->getObjectPath() === 'content') {
                for ($i = $lastParent; $i >= 0; $i--) {
                    // Skip all non-viewhelpers
                    if (!$parents[$i] instanceof ViewHelperNode) {
                        continue;
                    }

                    // Check for f:format.raw
                    $uninitializedViewHelper = $parents[$i]->getUninitializedViewHelper();
                    if ($uninitializedViewHelper instanceof IntrospectionViewHelper &&
                        $uninitializedViewHelper->getViewhelperTagName() === 'f:format.raw'
                    ) {
                        break 2;
                    }
                }

                return false;
            }

            // Search for more occurances of content variable
            $result = $this->checkContentVariableContext($childNode, array_merge($parents, [$childNode]));
            if (!$result) {
                return false;
            }
        }

        return true;
    }
}
