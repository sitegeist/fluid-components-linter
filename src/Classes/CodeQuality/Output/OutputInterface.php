<?php

declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Output;
use Symfony\Component\Console\Output\ConsoleOutput;

interface OutputInterface
{
    public static function output(ConsoleOutput $output, array $issues): void;
}
