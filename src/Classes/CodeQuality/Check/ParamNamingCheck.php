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
            foreach ($globalNamingConventions as $pattern) {
                if (!preg_match($this->createPattern($pattern), $name)) {
                    $results[] = new CodeQualityException(sprintf(
                        'Parameter does not follow naming convention "%s": %s',
                        $pattern,
                        $name
                    ));
                }
            }
            if (isset($extraNamingConventionsPerType[$type])) {
                foreach ($extraNamingConventionsPerType[$type] as $pattern) {
                    if (!preg_match($this->createPattern($pattern), $name)) {
                        $results[] = new CodeQualityException(sprintf(
                            'Parameter does not follow naming convention "%s" for type %s: %s',
                            $pattern,
                            $type,
                            $name
                        ));
                    }
                }
            }

            if (strlen($name) > $nameLength['max']) {
                $results[] = new CodeQualityException(sprintf(
                    'Parameter name is too long, only %d characters are allowed: %s',
                    $nameLength['max'],
                    $name
                ));
            }

            if (strlen($name) < $nameLength['min']) {
                $results[] = new CodeQualityException(sprintf(
                    'Parameter name is too short, at least %d characters are required: %s',
                    $nameLength['min'],
                    $name
                ));
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
