<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\CodeQuality\Component;
use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;
use Sitegeist\FluidComponentsLinter\ViewHelpers\IntrospectionViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class ViewHelpersCheck extends AbstractCheck
{
    public function check(): array
    {
        $viewHelperPatterns = $this->generateRestrictedViewHelpersPattern();
        $usedViewHelpers = $this->extractUsedViewHelpers();

        $issues = [];
        foreach ($viewHelperPatterns as $severity => $pattern) {
            $invalidViewHelpers = array_filter($usedViewHelpers, function (string $tagName) use ($pattern) {
                return (bool) preg_match($pattern, $tagName);
            });

            if (!empty($invalidViewHelpers)) {
                $issues[] = $this->issue(
                    'The following ViewHelpers are used in the renderer, but are not permitted: %s',
                    ['<' . implode('>, <', $invalidViewHelpers) . '>']
                )->setSeverity($severity);
            }
        }

        return $issues;
    }

    protected function generateRestrictedViewHelpersPattern(): array
    {
        $severities = [];
        foreach ($this->configuration['renderer']['viewHelperRestrictions'] as $restriction) {
            $pattern = preg_quote($restriction['viewHelperName'], '#');
            // Match group of ViewHelpers?
            if (substr($restriction['viewHelperName'], -1, 1) !== '.') {
                $pattern .= '$';
            }

            if (!isset($severities[$restriction['severity']])) {
                $severities[$restriction['severity']] = [];
            }
            $severities[$restriction['severity']][] = $pattern;
        }

        return array_map(function ($patterns) {
            return '#^(' . implode('|', $patterns) . ')#';
        }, $severities);
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
