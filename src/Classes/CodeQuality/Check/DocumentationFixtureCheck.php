<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality\Check;

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

        $issues = [];

        $documentationFile = sprintf($this->documentationFile, $directory, $name);
        if ($requireDocumentation['check'] && !file_exists($documentationFile)) {
            $issues[] = $this->issue('The component is missing a documentation file')
                ->setSeverity($requireDocumentation['severity']);
        }

        if (!$requireFixtureFile['check'] && !$requireDocumentationWithFixtureFile['check']) {
            return $issues;
        }

        $fixtureFiles = array_map(function ($fixtureFile) use ($directory, $name) {
            return sprintf($fixtureFile, $directory, $name);
        }, $this->fixtureFiles);
        $fixtureFile = array_filter($fixtureFiles, 'file_exists');
        $fixtureFile = reset($fixtureFile);

        if ($requireFixtureFile['check'] && !$fixtureFile) {
            $issues[] = $this->issue('The component is missing a fixture file')
                ->setSeverity($requireFixtureFile['severity']);
        }

        if (!$requireDocumentation['check'] &&
            $requireDocumentationWithFixtureFile['check'] &&
            $fixtureFile && !file_exists($documentationFile)
        ) {
            $issues[] = $this->issue('The component needs a documentation file because a fixture file is present')
                ->setSeverity($requireDocumentationWithFixtureFile['severity']);
        }

        return $issues;
    }
}
