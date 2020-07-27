<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

use Sitegeist\FluidComponentsLinter\Exception\CodeQualityException;

class DocumentationFixtureCheck extends AbstractCheck
{
    protected $fixtureFiles = [
        '%s/%s.fixture.json',
        '%s/%s.fixture.json5',
        '%s/%s.fixture.yml',
        '%s/%s.fixture.yaml'
    ];
    protected $documentationFile = '%s/%s.md';
    protected $componentExtension = '.html';

    public function check(): array
    {
        $requireFixtureFile = $this->configuration['component']['requireFixtureFile'];
        $requireDocumentation = $this->configuration['component']['requireDocumentation'];
        $requireDocumentationWithFixtureFile = $this->configuration['component']['requireDocumentationWithFixtureFile'];

        $directory = dirname($this->component->path);
        $name = basename($this->component->path, $this->componentExtension);

        $results = [];

        $documentationFile = sprintf($this->documentationFile, $directory, $name);
        if ($requireDocumentation && !file_exists($documentationFile)) {
            $results[] = new CodeQualityException('The component is missing a documentation file', 1595885141);
        }

        if (!$requireFixtureFile && !$requireDocumentationWithFixtureFile) {
            return $results;
        }

        $fixtureFiles = array_map(function ($fixtureFile) use ($directory, $name) {
            return sprintf($fixtureFile, $directory, $name);
        }, $this->fixtureFiles);
        $fixtureFile = array_filter($fixtureFiles, 'file_exists');
        $fixtureFile = reset($fixtureFile);

        if ($requireFixtureFile && !$fixtureFile) {
            $results[] = new CodeQualityException('The component is missing a fixture file', 1595885707);
        }

        if (
            !$requireDocumentation
            && $requireDocumentationWithFixtureFile
            && $fixtureFile && !file_exists($documentationFile)
        ) {
            $results[] = new CodeQualityException(
                'The component needs a documentation file because a fixture file is present',
                1595885708
            );
        }

        return $results;
    }
}
