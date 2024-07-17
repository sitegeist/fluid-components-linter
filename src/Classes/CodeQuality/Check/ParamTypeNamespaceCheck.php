<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

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
            if (str_starts_with((string) $arguments['type'], '\\')) {
                $issues[] = $this->issue('Type is specified with leading backslash for parameter %s: %s', [
                    $arguments['name'],
                    $arguments['type']
                ]);
            }
        }

        return $issues;
    }
}
