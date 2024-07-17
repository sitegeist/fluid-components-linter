<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Issue;

interface IssueInterface
{
    public const SEVERITY_INFO = 'info';
    public const SEVERITY_MINOR = 'minor';
    public const SEVERITY_MAJOR = 'major';
    public const SEVERITY_CRITICAL = 'critical';
    public const SEVERITY_BLOCKER = 'blocker';

    /**
     * Ordered list of severities based on their... severity
     */
    public const SEVERITIES = [
        self::SEVERITY_INFO,
        self::SEVERITY_MINOR,
        self::SEVERITY_MAJOR,
        self::SEVERITY_CRITICAL,
        self::SEVERITY_BLOCKER
    ];

    public function __construct(string $description, array $data, string $file, int $line = null, int $column = null);
    public function getCheckName(): string;
    public function getFile(): string;
    public function getLine(): ?int;
    public function getColumn(): ?int;
    public function getCategories(): array;
    public function getSeverity(): string;
    public function getDescription(): string;
    public function getData(): array;
    public function getMessage(): string;
    public function getFingerprint(): string;
}
