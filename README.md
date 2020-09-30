# Fluid Components Linter

CLI tool to validate your Fluid Components based on a specific ruleset for
code quality.

## Features

* check basic Fluid syntax
* check correct component structure (e. g. correct nesting of component ViewHelpers)
* enforce naming scheme and min/max length of parameter names
* limit parameter count per component (which should lead to simpler components)
* enforce parameter descriptions
* enforce presence of markdown documentation and fixture files used by Fluid Styleguide
* normalize syntax of namespaces in parameter type
* encourage strict data types instead of generic `array` or `object`
* suggest usage of correct types if parameter names contain hints like `link` or `image`
* enforce that component prefixer and `class` param are used
* enforce that certain ViewHelpers can't be used inside components
* enforce that `content` param is always wrapped in `<f:format.raw>`

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

For convenience, a file named `.fclint.json` in the current working directory will be picked
up automatically and doesn't need to be specified with `-c`.

Your adjusted configuration will be merged with the selected configuration preset, so you
only need to specify the rules you want to change. To make the following changes to
the default rules:

* don't require a markdown documentation file if a fixture file is present
* limit length of parameter names to 30 characters (default is 40)
* ignore all components inside a folder called `Template/`

you would use the following configuration file:

*.fclint.json:*

```json
{
    "files": {
        "ignorePatterns": [
            "**/Template/**"
        ]
    },
    "component": {
        "requireDocumentationWithFixtureFile": {
            "check": false
        }
    },
    "params": {
        "nameLength": {
            "max": 30
        }
    }
}
```

## Available Command Line Options

There are a few options that can be specified:

    $ fclint lint --help
    Description:
    Validates fluid components based on a specified ruleset

    Usage:
    lint [options] [--] <paths>...

    Arguments:
    paths                        Component files that should be included

    Options:
    -e, --extension[=EXTENSION]  Component file extension [default: ".html"]
    -p, --preset[=PRESET]        Name of configuration preset [default: false]
    -c, --config[=CONFIG]        Path to custom configuration file (.fclint.json in the current working directory will be picked up automatically) [default: false]
        --severity[=SEVERITY]    Minimum severity, all issues below this severity will be skipped. Possible values: info, minor, major, critical, blocker [default: "info"]
    -i, --ignore[=IGNORE]        Glob pattern that defines which files should be skipped (multiple values allowed)
        --json                   Output results as json (compatible to codeclimate spec)
