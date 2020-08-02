<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;

class ComponentVariablesCheck extends AbstractCheck
{
    public function check(): array
    {
        $usedVariableNames = $this->extractUsedVariables();

        $issues = [];

        if ($this->configuration['renderer']['requireComponentPrefixer']['check'] &&
            !in_array('component.class', $usedVariableNames) &&
            !in_array('component.prefix', $usedVariableNames)
        ) {
            $issues[] = $this->issue('Prefixed css classes should be used within components.')
                ->setSeverity($this->configuration['renderer']['requireComponentPrefixer']['severity']);
        }

        if ($this->configuration['renderer']['requireClass']['check'] &&
            !in_array('class', $usedVariableNames)) {
            $issues[] = $this->issue('It should be possible to set additional css classes via {class} variable.')
                ->setSeverity($this->configuration['renderer']['requireClass']['severity']);
        }

        return $issues;
    }

    protected function extractUsedVariables(): array
    {
        $usedVariables = $this->fluidService->extractNodeType(
            $this->component->rootNode,
            ObjectAccessorNode::class
        );
        return array_map(function (ObjectAccessorNode $node) {
            return $node->getObjectPath();
        }, $usedVariables);
    }
}
