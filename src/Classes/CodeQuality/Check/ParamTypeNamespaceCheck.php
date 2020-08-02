<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;

class ParamTypeNamespaceCheck extends AbstractCheck
{
    public function check(): array
    {
        if (!$this->configuration['params']['requireNamespaceWithoutLeadingSlash']['check']) {
            return [];
        }
        $this->setDefaultSeverity($this->configuration['params']['requireNamespaceWithoutLeadingSlash']['severity']);

        $issues = [];
        foreach ($this->component->paramNodes as $paramNode) {
            $arguments = $paramNode->getArguments();
            if (substr((string) $arguments['type'], 0, 1) === '\\') {
                $issues[] = $this->issue('Type is specified with leading backslash for parameter %s: %s', [
                    $arguments['name'],
                    $arguments['type']
                ]);
            }
        }

        return $issues;
    }
}
