<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\CodeQuality\Component;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\Issue;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;
use Sitegeist\FluidComponentsLinter\Service\FluidService;

abstract class AbstractCheck implements CheckInterface
{
    protected $component;
    protected $configuration;
    protected $fluidService;

    protected $defaultSeverity = IssueInterface::SEVERITY_MAJOR;

    public function __construct(Component $component, array $configuration = null)
    {
        $this->component = $component;
        $this->configuration = $configuration ?? $component->configuration;

        $this->fluidService = new FluidService;
    }

    protected function setDefaultSeverity(string $severity): self
    {
        $this->defaultSeverity = $severity;
        return $this;
    }

    protected function issue(string $message, array $data = [], int $line = null, int $column = null): Issue
    {
        $issue = new Issue($message, $data, $this->component->path, $line, $column);
        return $issue
            ->setCheckName(static::class)
            ->setSeverity($this->defaultSeverity);
    }
}
