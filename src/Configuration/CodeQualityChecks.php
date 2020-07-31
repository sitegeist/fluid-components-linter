<?php

use Sitegeist\FluidComponentsLinter\CodeQuality\Check;

return [
    Check\ComponentVariablesCheck::class,
    Check\ParamNamingCheck::class,
    Check\ParamCountCheck::class,
    Check\ParamDescriptionCheck::class,
    Check\ParamTypeNamespaceCheck::class,
    Check\DocumentationFixtureCheck::class,
    Check\ViewHelpersCheck::class,
];
