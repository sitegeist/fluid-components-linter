<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

class ParamTypeCheck extends AbstractCheck
{
    public function check(): array
    {
        $issues = [];
        foreach ($this->component->paramNodes as $paramNode) {
            $arguments = $paramNode->getArguments();
            $type = ltrim((string) $arguments['type'], '\\');
            $name = (string) $arguments['name'];
            $issuePrefix = sprintf('Parameter %s: ', $name);

            foreach ($this->configuration['params']['typeHints'] as $config) {
                $pattern = $this->createPattern($config['namePattern']);
                if (preg_match($pattern, $name)) {
                    $issues[] = $this->issue($issuePrefix . $config['message'], [
                        $config['typeHint']
                    ])->setSeverity($config['severity']);
                }
            }

            foreach ($this->configuration['params']['typeAlternatives'] as $config) {
                if ($config['typeValue'] === $type) {
                    $issues[] = $this->issue($issuePrefix . $config['message'], [
                        $config['typeAlternative'] ?? '',
                        $type
                    ])->setSeverity($config['severity']);
                }
            }
        }

        return $issues;
    }

    protected function createPattern(string $patternName): string
    {
        return '#' . ($this->configuration['_patterns'][$patternName] ?? $patternName) . '#';
    }
}
