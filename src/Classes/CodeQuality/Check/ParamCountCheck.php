<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;

class ParamCountCheck extends AbstractCheck
{
    public function check(): void
    {
        $count = $this->configuration['params']['count'];

        if (count($this->component->paramNodes) > $count['max']) {
            throw new CodeQualityException(sprintf(
                'The component has %d parameters, but only %d are allowed.',
                count($this->component->paramNodes),
                $count['max']
            ));
        }

        if (count($this->component->paramNodes) < $count['min']) {
            throw new CodeQualityException(sprintf(
                'The component has %d parameters, but at least %d are required.',
                count($this->component->paramNodes),
                $count['min']
            ));
        }
    }
}
