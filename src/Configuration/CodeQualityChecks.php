<?php

use Sitegeist\FluidComponentsLinter\CodeQuality\Check;

return [
    // Disabled: This is more complicated due to f:variable, f:for...
    //Check\ComponentVariablesCheck::class,
    Check\ParamNamingCheck::class,
    Check\ParamCountCheck::class,
    Check\ParamDescriptionCheck::class,
    Check\ParamTypeNamespaceCheck::class,
    Check\DocumentationFixtureCheck::class,
];
