<?php

declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Tests\Unit;

use Sitegeist\FluidComponentsLinter\CodeQuality\Check\ComponentVariablesCheck;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;

final class ComponentVariablesCheckTest extends AbstractTest
{
    public function dataProvider()
    {
        return [
            'requirePrefixerPrefixUsed' => [
                $this->generateConfiguration(true, IssueInterface::SEVERITY_MAJOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{component.prefix}</fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'requirePrefixerClassUsed' => [
                $this->generateConfiguration(true, IssueInterface::SEVERITY_MAJOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{component.class}</fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'requirePrefixerMinorSeverity' => [
                $this->generateConfiguration(true, IssueInterface::SEVERITY_MINOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MINOR
            ],
            'requirePrefixerDisabled' => [
                $this->generateConfiguration(false, IssueInterface::SEVERITY_MAJOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],

            'requireClassClassUsed' => [
                $this->generateConfiguration(false, IssueInterface::SEVERITY_MAJOR, true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{class}</fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'requireClassMinorSeverity' => [
                $this->generateConfiguration(false, IssueInterface::SEVERITY_MAJOR, true, IssueInterface::SEVERITY_MINOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MINOR
            ],
            'requireClassDisabled' => [
                $this->generateConfiguration(false, IssueInterface::SEVERITY_MAJOR, false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],

            'combination' => [
                $this->generateConfiguration(true, IssueInterface::SEVERITY_MAJOR, true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                2,
                IssueInterface::SEVERITY_MAJOR
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
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

    public function generateConfiguration(
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
