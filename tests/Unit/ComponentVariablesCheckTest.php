<?php

declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sitegeist\FluidComponentsLinter\CodeQuality\Check\ComponentVariablesCheck;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;

final class ComponentVariablesCheckTest extends AbstractTestClass
{
    public static function dataProvider()
    {
        return [
            'requirePrefixerPrefixUsed' => [
                static::generateConfiguration(true, IssueInterface::SEVERITY_MAJOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{component.prefix}</fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'requirePrefixerClassUsed' => [
                static::generateConfiguration(true, IssueInterface::SEVERITY_MAJOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{component.class}</fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'requirePrefixerMinorSeverity' => [
                static::generateConfiguration(true, IssueInterface::SEVERITY_MINOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MINOR
            ],
            'requirePrefixerDisabled' => [
                static::generateConfiguration(false, IssueInterface::SEVERITY_MAJOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],

            'requireClassClassUsed' => [
                static::generateConfiguration(false, IssueInterface::SEVERITY_MAJOR, true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{class}</fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'requireClassMinorSeverity' => [
                static::generateConfiguration(false, IssueInterface::SEVERITY_MAJOR, true, IssueInterface::SEVERITY_MINOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MINOR
            ],
            'requireClassDisabled' => [
                static::generateConfiguration(false, IssueInterface::SEVERITY_MAJOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],

            'combination' => [
                static::generateConfiguration(true, IssueInterface::SEVERITY_MAJOR, true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                2,
                IssueInterface::SEVERITY_MAJOR
            ]
        ];
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function testChecksForMinimumMaximumParameterCount(
        array $configuration,
        string $componentSource,
        int $expectedIssueCount,
        string $expectedSeverity
    ): void {
        $checkInstance = new ComponentVariablesCheck($this->createComponent($componentSource), $configuration);
        $issues = $checkInstance->check();

        $this->assertCount($expectedIssueCount, $issues);
        foreach ($issues as $issue) {
            $this->assertEquals($expectedSeverity, $issue->getSeverity());
        }
    }

    public static function generateConfiguration(
        bool $requirePrefixer,
        string $requirePrefixerSeverity,
        bool $requireClass,
        string $requireClassSeverity
    ): array {
        return [
            'renderer' => [
                'requireComponentPrefixer' => [
                    'check' => $requirePrefixer,
                    'severity' => $requirePrefixerSeverity
                ],
                'requireClass' => [
                    'check' => $requireClass,
                    'severity' => $requireClassSeverity
                ]
            ]
        ];
    }
}
