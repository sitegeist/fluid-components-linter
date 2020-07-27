<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\CodeQuality\Component;

interface CheckInterface
{
    /**
     * @param Component $component  Component that should be checked
     * @param array $configuration  Validated linting configuration
     */
    public function __construct(Component $component, array $configuration);

    /**
     * Performs a code quality check on the component
     * If the check fails, the method can either throw one exception
     * or return an array of exceptions
     *
     * @throws \Sitegeist\FluidComponentsLinter\Exception\CodeQualityException
     * @return array|void
     */
    public function check();
}
