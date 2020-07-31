<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\CodeQuality\Component;
use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;
use Sitegeist\FluidComponentsLinter\ViewHelpers\IntrospectionViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class ViewHelpersCheck extends AbstractCheck
{
    public function check(): void
    {
        $viewHelperPattern = $this->generateRestrictedViewHelpersPattern();

        $invalidViewHelpers = array_filter(
            $this->extractUsedViewHelpers(),
            function (string $tagName) use ($viewHelperPattern) {
                return (bool) preg_match($viewHelperPattern, $tagName);
            }
        );

        if (!empty($invalidViewHelpers)) {
            throw new CodeQualityException(sprintf(
                'The following ViewHelpers are used in the renderer, but are not permitted: %s',
                '<' . implode('>, <', $invalidViewHelpers) . '>'
            ), 1596220321);
        }
    }

    protected function generateRestrictedViewHelpersPattern(): string
    {
        $viewHelperRestrictions = array_map(function (array $config) {
            $pattern = preg_quote($config['viewHelperName'], '#');
            // Match group of ViewHelpers?
            if (substr($config['viewHelperName'], -1, 1) !== '.') {
                $pattern .= '$';
            }
            return $pattern;
        }, $this->configuration['renderer']['viewHelperRestrictions']);
        return '#^(' . implode('|', $viewHelperRestrictions) . ')#';
    }

    protected function extractUsedViewHelpers(): array
    {
        $usedViewHelpers = $this->fluidService->extractNodeType(
            $this->component->rootNode,
            ViewHelperNode::class
        );
        $introspectedViewHelpers = array_filter($usedViewHelpers, function (ViewHelperNode $node) {
            return $node->getUninitializedViewHelper() instanceof IntrospectionViewHelper;
        });
        return array_map(function (ViewHelperNode $node) {
            return $node->getUninitializedViewHelper()->getViewhelperTagName();
        }, $introspectedViewHelpers);
    }
}
