<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;

class ParamTypeNamespaceCheck extends AbstractCheck
{
    public function check(): array
    {
        if (!$this->configuration['params']['requireNamespaceWithoutLeadingSlash']) {
            return [];
        }

        $results = [];
        foreach ($this->component->paramNodes as $paramNode) {
            $arguments = $paramNode->getArguments();
            if (substr((string) $arguments['type'], 0, 1) === '\\') {
                $results[] = new CodeQualityException(sprintf(
                    'Type is specified with leading backslash for parameter %s: %s',
                    $arguments['name'],
                    $arguments['type']
                ), 1595883001);
            }
        }

        return $results;
    }
}
