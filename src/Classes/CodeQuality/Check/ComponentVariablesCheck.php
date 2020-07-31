<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;

class ComponentVariablesCheck extends AbstractCheck
{
    public function check(): array
    {
        $usedVariableNames = $this->extractUsedVariables();

        $results = [];

        if ($this->configuration['renderer']['requireComponentPrefixer'] &&
            !in_array('component.class', $usedVariableNames) &&
            !in_array('component.prefix', $usedVariableNames)
        ) {
            $results[] = new CodeQualityException(
                'Prefixed css classes should be used within components.',
                1596218583
            );
        }

        if ($this->configuration['renderer']['requireClass'] &&
            !in_array('class', $usedVariableNames)) {
            $results[] = new CodeQualityException(
                'It should be possible to set additional css classes via {class} variable.',
                1596218584
            );
        }

        return $results;
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
