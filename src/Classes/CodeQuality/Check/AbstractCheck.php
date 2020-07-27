<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\CodeQuality\Component;
use Sitegeist\FluidComponentsLinter\Service\FluidService;

abstract class AbstractCheck implements CheckInterface
{
    protected $component;
    protected $configuration;
    protected $fluidService;

    public function __construct(Component $component, array $configuration)
    {
        $this->component = $component;
        $this->configuration = $configuration;

        $this->fluidService = new FluidService;
    }
}
