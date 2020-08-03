<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Output;

use Sitegeist\FluidComponentsLinter\CodeQuality\Issue\Issue;
use Symfony\Component\Console\Output\ConsoleOutput;

class JsonOutput implements OutputInterface
{
    public static function output(ConsoleOutput $output, array $issues): void
    {
        $output->write(json_encode(array_map(function (Issue $issue) {
            $lines = $positions = null;
            if ($issue->getLine()) {
                if ($issue->getColumn()) {
                    $lines = [
                        'begin' => $issue->getLine(),
                        'end' => $issue->getLine()
                    ];
                    $positions = [
                        'begin' => [
                            'line' => $issue->getLine(),
                            'column' => $issue->getColumn()
                        ]
                    ];
                } else {
                    $lines = [
                        'begin' => $issue->getLine(),
                        'end' => $issue->getLine()
                    ];
                }
            } else {
                $lines = [
                    'begin' => 1,
                    'end' => count(file($issue->getFile()))
                ];
            }

            return [
                'type' => 'issue',
                'check_name' => $issue->getCheckName(),
                'description' => $issue->getMessage(),
                'categories' => $issue->getCategories(),
                'location' => array_filter([
                    'path' => str_replace(getcwd() . '/', '', $issue->getFile()),
                    'lines' => $lines,
                    'positions' => $positions
                ]),
                'severity' => $issue->getSeverity(),
                'fingerprint' => $issue->getFingerprint()
            ];
        }, $issues)));
    }
}
