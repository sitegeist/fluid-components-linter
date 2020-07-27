<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\CodeQuality\Component;
use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class ComponentVariablesCheck extends AbstractCheck
{
    protected $predefinedVariables = [
        'settings(\.|$)',
        'class$',
        'content$',
        'component\.(class|prefix|namespace)$'
    ];

    public function check(): void
    {
        $allowedVariables = array_merge($this->predefinedVariables, $this->generateParamNamePatterns());
        $allowedVariablesPattern = '#^(' . implode('|', $allowedVariables) . ')#';

        $usedVariables = $this->fluidService->extractNodeType(
            $this->component->rootNode,
            ObjectAccessorNode::class
        );
        $usedVariableNames = array_map(function (ObjectAccessorNode $node) {
            return $node->getObjectPath();
        }, $usedVariables);

        $invalidVariables = array_filter(
            $usedVariableNames,
            function (string $usedVariable) use ($allowedVariablesPattern) {
                return !preg_match($allowedVariablesPattern, $usedVariable);
            }
        );

        if (!empty($invalidVariables)) {
            throw new CodeQualityException(sprintf(
                'The following variables are used in the component, but were never defined: %s',
                '{' . implode('}, {', $invalidVariables) . '}'
            ), 1595870402);
        }
    }

    protected function generateParamNamePatterns(): array
    {
        return array_map(function (ViewHelperNode $node) {
            return preg_quote((string) $node->getArguments()['name'], '#') . '(\.|$)';
        }, $this->component->paramNodes);
    }
}
