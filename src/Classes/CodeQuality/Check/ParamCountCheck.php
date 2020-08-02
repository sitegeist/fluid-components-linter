<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

class ParamCountCheck extends AbstractCheck
{
    public function check(): array
    {
        $this->setDefaultSeverity($this->configuration['params']['count']['severity']);
        $count = $this->configuration['params']['count'];
        $issues = [];

        if (count($this->component->paramNodes) > $count['max']) {
            $issues[] = $this->issue('The component has %d parameters, but only %d are allowed.', [
                count($this->component->paramNodes),
                $count['max']
            ]);
        }

        if (count($this->component->paramNodes) < $count['min']) {
            $issues[] = $this->issue('The component has %d parameters, but at least %d are required.', [
                count($this->component->paramNodes),
                $count['min']
            ]);
        }

        return $issues;
    }
}
