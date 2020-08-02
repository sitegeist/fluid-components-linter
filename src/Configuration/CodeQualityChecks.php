<?php

use Sitegeist\FluidComponentsLinter\CodeQuality\Check;

return [
    Check\DocumentationFixtureCheck::class,
    Check\ParamNamingCheck::class,
    Check\ParamCountCheck::class,
    Check\ParamTypeNamespaceCheck::class,
    Check\ParamDescriptionCheck::class,
    Check\ComponentVariablesCheck::class,
    Check\ContentVariableCheck::class,
    Check\ViewHelpersCheck::class,
];
