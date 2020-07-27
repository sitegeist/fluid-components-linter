<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class ParamDescriptionCheck extends AbstractCheck
{
    public function check(): array
    {
        $requireDescriptionGlobal = $this->configuration['params']['requireDescription'];
        $requireDescriptionForType = $this->configuration['params']['requireDescriptionForType'];

        $results = [];
        foreach ($this->component->paramNodes as $paramNode) {
            $arguments = $paramNode->getArguments();
            $description = $arguments['description'] ?? '';
            $type = (string) $arguments['type'];

            if (trim((string) $description) !== '') {
                continue;
            }

            if ($requireDescriptionGlobal) {
                $results[] = new CodeQualityException(sprintf(
                    'Missing required description for parameter: %s',
                    $arguments['name']
                ), 1595883576);
            }

            if (isset($requireDescriptionForType[$type])) {
                $results[] = new CodeQualityException(sprintf(
                    'Missing required description for %s parameter: %s',
                    $type,
                    $arguments['name']
                ), 1595883578);
            }
        }

        return $results;
    }
}
