<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Output;

use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\Issue;
use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\IssueInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class GroupedOutput implements OutputInterface
{
    protected static $fileTemplate = '<fg=black;bg=white;options=bold>%s</>';
    protected static $templates = [
        IssueInterface::SEVERITY_INFO => '%4d  INFO: %s',
        IssueInterface::SEVERITY_MINOR => '%4d  <fg=yellow;options=bold>MINOR: %s</>',
        IssueInterface::SEVERITY_MAJOR => '%4d  <fg=red;options=bold>MAJOR: %s</>',
        IssueInterface::SEVERITY_CRITICAL => '%4d  <fg=white;bg=red;options=bold>CRITICAL: %s</>',
        IssueInterface::SEVERITY_BLOCKER => '%4d  <fg=white;bg=red;options=bold>BLOCKER: %s</>',
    ];

    public static function output(ConsoleOutput $output, array $issues): void
    {
        if (empty($issues)) {
            $output->writeln('<info>Everything looks fine</info>');
            return;
        }

        $groupedIssues = [];
        foreach ($issues as $issue) {
            if (!isset($groupedIssues[$issue->getFile()])) {
                $groupedIssues[$issue->getFile()] = [];
                if (!isset($groupedIssues[$issue->getFile()][$issue->getSeverity()])) {
                    $groupedIssues[$issue->getFile()][$issue->getSeverity()] = [];
                }
            }
            $groupedIssues[$issue->getFile()][$issue->getSeverity()][] = $issue;
        }

        foreach ($groupedIssues as $file => $severities) {
            $section = $output->section();
            $section->writeln([
                ' ',
                sprintf(self::$fileTemplate, str_replace(getcwd() . '/', '', $file)),
                '============='
            ]);
            $i = 1;
            foreach ($severities as $issues) {
                foreach ($issues as $issue) {
                    $section->writeln(sprintf(
                        self::$templates[$issue->getSeverity()],
                        $i++,
                        $issue->getMessage()
                    ));
                }
            }
            $section->writeln([' ']);
        }
    }
}
