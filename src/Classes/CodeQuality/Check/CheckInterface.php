<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\CodeQuality\Component;

interface CheckInterface
{
    public function __construct(Component $component, array $configuration);
    public function check();
}
