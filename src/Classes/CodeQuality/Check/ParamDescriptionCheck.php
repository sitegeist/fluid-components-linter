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

        $issues = [];
        foreach ($this->component->paramNodes as $paramNode) {
            $arguments = $paramNode->getArguments();
            $type = (string) $arguments['type'];

            if (isset($arguments['description']) && !$this->fluidService->isEmptyTextNode($arguments['description'])) {
                continue;
            }

            if ($requireDescriptionGlobal['check']) {
                $issues[] = $this->issue('Missing required description for parameter: %s', [
                    $arguments['name']
                ])->setSeverity($requireDescriptionGlobal['severity']);
            }

            if (!empty($requireDescriptionForType[$type]['requireDescription'])) {
                $issues[] = $this->issue('Missing required description for %s parameter: %s', [
                    $type,
                    $arguments['name']
                ])->setSeverity($requireDescriptionForType[$type]['severity']);
            }
        }

        return $issues;
    }
}
