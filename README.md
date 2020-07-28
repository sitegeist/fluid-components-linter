# Fluid Components Linter

CLI tool to validate your Fluid Components based on a specific ruleset for
code quality.

**Note: this tool is currently still under heavy development!**

## Features

* check basic Fluid syntax
* check correct component structure (e. g. correct nesting of component ViewHelpers)
* enforce naming scheme and min/max length of parameter names
* limit parameter count per component (which should lead to simpler components)
* enforce parameter descriptions
* enforce presence of markdown documentation and fixture files used by Fluid Styleguide
* normalize syntax of namespaces in parameter type

see [CodeQualityChecks.php](./src/Configuration/CodeQualityChecks.php)

## Getting Started

To use the linter, require this package as a dev dependency via composer:

    composer req --dev sitegeist/fluid-components-linter

The package provides the binary `fclint` which can be used to validate
individual component files as well as whole directory structures containing
component files.

    fclint lint Resources/Private/Components/

## Customize Ruleset

If you want to modify the code quality ruleset, you can overwrite the
predefined rules in [default.fclint.json](./src/Configuration/default.fclint.json):

    fclint lint -c ./myRules.fclint.json Resources/Private/Components/

Both configuration files are merged by `fclint`, so you only need to specify the rules you want to change. To make the following changes to the default rules:

* don't require a markdown documentation file if a fixture file is present
* limit length of parameter names to 30 characters (default is 40)

you would use the following configuration file:

*myRules.fclint.json:*

```json
{
    "component": {
        "requireDocumentationWithFixtureFile": false
    },
    "params": {
        "nameLength": {
            "max": 30
        }
    }
}
```

Note that currently not all rules defined in [default.fclint.json](./src/Configuration/default.fclint.json) are implemented yet.
