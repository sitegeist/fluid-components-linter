<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Issue;

class Issue implements IssueInterface
{
    protected string $checkName = '';

    protected array $categories = [];
    protected string $severity = IssueInterface::SEVERITY_MAJOR;

    public function __construct(
        protected string $description,
        protected array $data,
        protected string $file,
        protected ?int $line = null,
        protected ?int $column = null,
    ) {
        $this->setLocation($file, $line, $column);
    }

    public function setCheckName(string $checkName): self
    {
        $this->checkName = $checkName;
        return $this;
    }

    public function getCheckName(): string
    {
        return $this->checkName;
    }

    public function setLocation(string $file, ?int $line = null, ?int $column = null): self
    {
        $this->file = $file;
        $this->line = $line ?? $this->line;
        $this->column = $column ?? $this->column;
        return $this;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getColumn(): ?int
    {
        return $this->column;
    }

    public function addCategory(string $category): self
    {
        $this->categories[] = $category;
        return $this;
    }

    public function setCategories(array $categories): self
    {
        $this->categories = $categories;
        return $this;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setSeverity(string $severity): self
    {
        $this->severity = $severity;
        return $this;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMessage(): string
    {
        return (!empty($this->data))
            ? vsprintf($this->description, $this->data)
            : $this->description;
    }

    public function getFingerprint(): string
    {
        return hash('sha256', json_encode([
            $this->getCheckName(),
            $this->file,
            $this->line,
            $this->column,
            $this->description,
            $this->data
        ]));
    }
}
