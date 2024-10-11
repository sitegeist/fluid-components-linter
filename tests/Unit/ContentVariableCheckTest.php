<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sitegeist\FluidComponentsLinter\CodeQuality\Check\ContentVariableCheck;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;

final class ContentVariableCheckTest extends AbstractTestClass
{
    public static function dataProvider()
    {
        return [
            'rawViewHelper' => [
                static::generateConfiguration(true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer><f:format.raw>{content}</f:format.raw></fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'rawInlineViewHelper' => [
                static::generateConfiguration(true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{content->f:format.raw()}</fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'nestedRawViewHelper' => [
                static::generateConfiguration(true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>
                    <f:format.raw>content<my:viewhelper>content{content}content</my:viewhelper>content</f:format.raw>
                </fc:renderer></fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'missingViewHelper' => [
                static::generateConfiguration(true, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{content}</fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MAJOR
            ],
            'minorSeverity' => [
                static::generateConfiguration(true, IssueInterface::SEVERITY_MINOR),
                '<fc:component><fc:renderer>{content}</fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MINOR
            ],
            'disabledCheck' => [
                static::generateConfiguration(false, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer>{content}</fc:renderer></fc:component>',
                0,
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
        $checkInstance = new ContentVariableCheck($this->createComponent($componentSource), $configuration);
        $issues = $checkInstance->check();

        $this->assertCount($expectedIssueCount, $issues);
        foreach ($issues as $issue) {
            $this->assertEquals($expectedSeverity, $issue->getSeverity());
        }
    }

    public static function generateConfiguration(bool $check, string $severity): array
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
