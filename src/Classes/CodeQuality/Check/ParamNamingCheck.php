<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class ParamNamingCheck extends AbstractCheck
{
    public function check(): array
    {
        $globalNamingConventions = $this->configuration['params']['namingConventions'];
        $extraNamingConventionsPerType = $this->configuration['params']['extraNamingConventionsPerType'];
        $nameLength = $this->configuration['params']['nameLength'];

        $params = $this->extractParamNamesAndTypes();

        $results = [];
        foreach ($params as $name => $type) {
            if (in_array($name, $nameLength['allowed'])) {
                continue;
            }

            if (in_array($name, $nameLength['denied'])) {
                $results[] = new CodeQualityException(sprintf(
                    'Parameter name is not allowed (denied names: %s): %s',
                    implode(', ', $nameLength['denied']),
                    $name
                ), 1596005526);
                continue;
            }

            foreach ($globalNamingConventions as $pattern) {
                if (!preg_match($this->createPattern($pattern), $name)) {
                    $results[] = new CodeQualityException(sprintf(
                        'Parameter does not follow naming convention "%s": %s',
                        $pattern,
                        $name
                    ), 1595883599);
                }
            }
            if (isset($extraNamingConventionsPerType[$type])) {
                foreach ($extraNamingConventionsPerType[$type]['namingConventions'] as $pattern) {
                    if (!preg_match($this->createPattern($pattern), $name)) {
                        $results[] = new CodeQualityException(sprintf(
                            'Parameter does not follow naming convention "%s" for type %s: %s',
                            $pattern,
                            $type,
                            $name
                        ), 1595883604);
                    }
                }
            }

            if (strlen($name) > $nameLength['max']) {
                $results[] = new CodeQualityException(sprintf(
                    'Parameter name is too long, only %d characters are allowed: %s',
                    $nameLength['max'],
                    $name
                ), 1595883613);
            }

            if (strlen($name) < $nameLength['min']) {
                $results[] = new CodeQualityException(sprintf(
                    'Parameter name is too short, at least %d characters are required: %s',
                    $nameLength['min'],
                    $name
                ), 1595883621);
            }
        }

        return $results;
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
