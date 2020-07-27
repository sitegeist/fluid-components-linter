<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Service;

class ComponentService
{
    /**
     * Collection of paths that have already been scanned for components;
     * this prevents infinite loops caused by circular symlinks
     *
     * @var array
     */
    protected $scannedPaths;

    /**
     * Finds all components in the specified paths
     *
     * @param array $paths  Array of paths (either directory or path to a specific component file)
     * @param string $ext   File extension of components
     * @return array        Array of absolute paths to component files
     */
    public function findComponentsInPaths(array $paths, string $ext): array
    {
        $components = $this->scannedPaths = [];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                if (file_exists($path) && substr($path, - strlen($ext)) == $ext) {
                    $components[] = $path;
                }
                continue;
            }

            $components = array_merge(
                $components,
                $this->scanForComponents($path, $ext)
            );
        }
        return $components;
    }

    /**
     * Searches recursively for component files in a directory
     *
     * @param string $path
     * @param string $ext
     * @return array
     */
    protected function scanForComponents(string $path, string $ext): array
    {
        $components = [];

        $componentCandidates = scandir($path);
        foreach ($componentCandidates as $componentName) {
            // Skip relative links
            if ($componentName === '.' || $componentName === '..') {
                continue;
            }

            // Only search for directories and prevent infinite loops
            $componentPath = realpath($path . DIRECTORY_SEPARATOR . $componentName);
            if (!is_dir($componentPath) || isset($this->scannedPaths[$componentPath])) {
                continue;
            }
            $this->scannedPaths[$componentPath] = true;

            $componentFile = $componentPath . DIRECTORY_SEPARATOR . $componentName . $ext;

            // Only match folders that contain a component file
            if (file_exists($componentFile)) {
                $components[] = $componentFile;
            }

            // Continue recursively
            $components = array_merge(
                $components,
                $this->scanForComponents($componentPath, $ext, $this->scannedPaths)
            );
        }

        return $components;
    }
}
