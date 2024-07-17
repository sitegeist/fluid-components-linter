<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\ViewHelpers\IntrospectionViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class ViewHelpersCheck extends AbstractCheck
{
    public function check(): array
    {
        $usedViewHelpers = $this->extractUsedViewHelpers();

        $issues = [];
        foreach ($this->configuration['renderer']['viewHelperRestrictions'] as $restriction) {
            $pattern = $this->createViewHelperPattern($restriction['viewHelperName']);
            foreach ($usedViewHelpers as $tagName) {
                if (preg_match($pattern, (string) $tagName)) {
                    $issues[] = $this->issue($restriction['message'], [
                        $tagName
                    ])->setSeverity($restriction['severity']);
                }
            }
        }

        return $issues;
    }

    protected function createViewHelperPattern(string $viewHelperName): string
    {
        $pattern = preg_quote($viewHelperName, '#');
        if (!str_ends_with($viewHelperName, '.')) {
            $pattern .= '$';
        }
        return '#^' . $pattern . '#';
    }

    protected function extractUsedViewHelpers(): array
    {
        $usedViewHelpers = $this->fluidService->extractNodeType(
            $this->component->rootNode,
            ViewHelperNode::class
        );
        $introspectedViewHelpers = array_filter($usedViewHelpers, fn(ViewHelperNode $node) => $node->getUninitializedViewHelper() instanceof IntrospectionViewHelper);
        return array_map(function (ViewHelperNode $node) {
            /** @var IntrospectionViewHelper */
            $introspectionViewHelper = $node->getUninitializedViewHelper();
            return $introspectionViewHelper->getViewhelperTagName();
        }, $introspectedViewHelpers);
    }
}
