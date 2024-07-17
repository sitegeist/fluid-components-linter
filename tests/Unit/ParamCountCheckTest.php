<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sitegeist\FluidComponentsLinter\CodeQuality\Check\ParamCountCheck;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;

final class ParamCountCheckTest extends AbstractTestClass
{
    public static function dataProvider()
    {
        return [
            'tooFewParameters' => [
                static::generateConfiguration(3, 10, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MAJOR
            ],
            'parametersInAllowedRange' => [
                static::generateConfiguration(3, 5, IssueInterface::SEVERITY_MAJOR),
                '<fc:component>
                    <fc:param name="a" type="string" />
                    <fc:param name="b" type="string" />
                    <fc:param name="c" type="string" />
                    <fc:renderer></fc:renderer>
                </fc:component>',
                0,
                IssueInterface::SEVERITY_MAJOR
            ],
            'tooManyParameters' => [
                static::generateConfiguration(1, 5, IssueInterface::SEVERITY_MAJOR),
                '<fc:component>
                    <fc:param name="a" type="string" />
                    <fc:param name="b" type="string" />
                    <fc:param name="c" type="string" />
                    <fc:param name="d" type="string" />
                    <fc:param name="e" type="string" />
                    <fc:param name="f" type="string" />
                    <fc:renderer></fc:renderer>
                </fc:component>',
                1,
                IssueInterface::SEVERITY_MAJOR
            ],
            'minorSeverity' => [
                static::generateConfiguration(3, 10, IssueInterface::SEVERITY_MINOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MINOR
            ],
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
        $checkInstance = new ParamCountCheck($this->createComponent($componentSource), $configuration);
        $issues = $checkInstance->check();

        $this->assertCount($expectedIssueCount, $issues);
        foreach ($issues as $issue) {
            $this->assertEquals($expectedSeverity, $issue->getSeverity());
        }
    }

    public static function generateConfiguration(int $min, int $max, string $severity): array
    {
        return [
            'params' => [
                'count' => [
                    'min' => $min,
                    'max' => $max,
                    'severity' => $severity
                ]
            ]
        ];
    }
}
