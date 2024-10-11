<?php
declare(strict_types=1);

use Symfony\Component\Console\Application;
use Sitegeist\FluidComponentsLinter\Command\LintCommand;

call_user_func(function () {
    // Check for valid autoload configuration
    $autoloadLocations = [
        $GLOBALS['_composer_autoload_path'] ?? null,
        dirname(__DIR__) . '/vendor/autoload.php',
        dirname(__DIR__, 3) . '/autoload.php'
    ];
    $autoloadLocations = array_filter(array_filter($autoloadLocations), 'file_exists');

    if (empty($autoloadLocations)) {
        echo 'fluid-components-linter: Insufficient autoloading information';
        exit(1);
    }

    // Initialize autoloader
    $autoloadLocation = reset($autoloadLocations);
    require $autoloadLocation;

    // Setup console application
    $application = new Application();
    $application->add(new LintCommand());
    $application->run();
});
