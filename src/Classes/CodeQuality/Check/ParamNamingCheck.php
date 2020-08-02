<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class ParamNamingCheck extends AbstractCheck
{
    public function check(): array
    {
        $generalNamingConventions = $this->configuration['params']['generalNamingConventions'];
        $extraNamingConventionsPerType = $this->configuration['params']['extraNamingConventionsPerType'];
        $nameLength = $this->configuration['params']['nameLength'];

        $params = $this->extractParamNamesAndTypes();

        $issues = [];
        foreach ($params as $name => $type) {
            if (in_array($name, $nameLength['allowed'])) {
                continue;
            }

            if (in_array($name, $nameLength['denied'])) {
                $issues[] = $this->issue('Parameter name is not allowed (forbidden names: %s): %s', [
                    implode(', ', $nameLength['denied']),
                    $name
                ])->setSeverity($nameLength['severity']);
                continue;
            }

            foreach ($generalNamingConventions['namingConventions'] as $pattern) {
                if (!preg_match($this->createPattern($pattern), $name)) {
                    $issues[] = $this->issue('Parameter does not follow naming convention "%s": %s', [
                        $pattern,
                        $name
                    ])->setSeverity($generalNamingConventions['severity']);
                }
            }
            if (isset($extraNamingConventionsPerType[$type])) {
                foreach ($extraNamingConventionsPerType[$type]['namingConventions'] as $pattern) {
                    if (!preg_match($this->createPattern($pattern), $name)) {
                        $issues[] = $this->issue('Parameter does not follow naming convention "%s" for type %s: %s', [
                            $pattern,
                            $type,
                            $name
                        ])->setSeverity($extraNamingConventionsPerType[$type]['severity']);
                    }
                }
            }

            if (strlen($name) > $nameLength['max']) {
                $issues[] = $this->issue('Parameter name is too long, only %d characters are allowed: %s', [
                    $nameLength['max'],
                    $name
                ])->setSeverity($nameLength['severity']);
            }

            if (strlen($name) < $nameLength['min']) {
                $issues[] = $this->issue('Parameter name is too short, at least %d characters are required: %s', [
                    $nameLength['min'],
                    $name
                ])->setSeverity($nameLength['severity']);
            }
        }

        return $issues;
    }

    protected function extractParamNamesAndTypes(): array
    {
        return array_reduce($this->component->paramNodes, function (array $carry, ViewHelperNode $node) {
            $arguments = $node->getArguments();
            $carry[(string) $arguments['name']] = (string) $arguments['type'];
            return $carry;
        }, []);
    }

    protected function createPattern(string $patternName): string
    {
        return '#' . ($this->configuration['_patterns'][$patternName] ?? $patternName) . '#';
    }
}
