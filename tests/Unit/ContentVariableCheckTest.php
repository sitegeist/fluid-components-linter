<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Tests\Unit;

use Sitegeist\FluidComponentsLinter\CodeQuality\Check\ContentVariableCheck;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;

final class ContentVariableCheckTest extends AbstractTest
{
    public function dataProvider()
    {
        return [
            'rawViewHelper' => [
                $this->generateConfiguration(true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer><f:format.raw>{content}</f:format.raw></fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'rawInlineViewHelper' => [
                $this->generateConfiguration(true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{content->f:format.raw()}</fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'nestedRawViewHelper' => [
                $this->generateConfiguration(true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>
                    <f:format.raw>content<my:viewhelper>content{content}content</my:viewhelper>content</f:format.raw>
                </fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'missingViewHelper' => [
                $this->generateConfiguration(true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{content}</fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MAJOR
            ],
            'minorSeverity' => [
                $this->generateConfiguration(true, IssueInterface::SEVERITY_MINOR),
                '<fc:component><fc:renderer>{content}</fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MINOR
            ],
            'disabledCheck' => [
                $this->generateConfiguration(false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{content}</fc:renderer></fc:component>',
                0,
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
        $checkInstance = new ContentVariableCheck($this->createComponent($componentSource), $configuration);
        $issues = $checkInstance->check();

        $this->assertCount($expectedIssueCount, $issues);
        foreach ($issues as $issue) {
            $this->assertEquals($expectedSeverity, $issue->getSeverity());
        }
    }

    public function generateConfiguration(bool $check, string $severity): array
    {
        return [
            'renderer' => [
                'requireRawContent' => [
                    'check' => $check,
                    'severity' => $severity
                ]
            ]
        ];
    }
}
