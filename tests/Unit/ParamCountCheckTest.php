<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Tests\Unit;

use Sitegeist\FluidComponentsLinter\CodeQuality\Check\ParamCountCheck;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;

final class ParamCountCheckTest extends AbstractTest
{
    public function dataProvider()
    {
        return [
            'tooFewParameters' => [
                $this->generateConfiguration(3, 10, IssueInterface::SEVERITY_MAJOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MAJOR
            ],
            'parametersInAllowedRange' => [
                $this->generateConfiguration(3, 5, IssueInterface::SEVERITY_MAJOR),
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
                $this->generateConfiguration(1, 5, IssueInterface::SEVERITY_MAJOR),
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
                $this->generateConfiguration(3, 10, IssueInterface::SEVERITY_MINOR),
                '<fc:component><fc:renderer></fc:renderer></fc:component>',
                1,
                IssueInterface::SEVERITY_MINOR
            ],
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
        $checkInstance = new ParamCountCheck($this->createComponent($componentSource), $configuration);
        $issues = $checkInstance->check();

        $this->assertCount($expectedIssueCount, $issues);
        foreach ($issues as $issue) {
            $this->assertEquals($expectedSeverity, $issue->getSeverity());
        }
    }

    public function generateConfiguration(int $min, int $max, string $severity): array
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
