<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Service;

class ComponentService
{
    /**
     * Collection of paths that have already been scanned for components;
     * this prevents infinite loops caused by circular symlinks
     */
    protected array $scannedPaths = [];

    /**
     * Finds all components in the specified paths
     *
     * @param array $paths  Array of paths (either directory or path to a specific component file)
     * @param string $ext   File extension of components
     * @return array        Array of absolute paths to component files
     */
    public function findComponentsInPaths(array $paths, string $ext): array
    {
        $components = [];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                if (file_exists($path) && str_ends_with((string) $path, $ext)) {
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
     * Removes all items from the provided array of component paths
     * that match the provided ignore list
     */
    public function removeComponentsFromIgnoreList(array $components, array $ignoreList): array
    {
        if (empty($ignoreList)) {
            return $components;
        }

        $ignorePattern = static::buildPattern($ignoreList);
        if (!$ignorePattern) {
            throw new \Exception(sprintf(
                'Invalid ignore pattern provided: %s',
                print_r($ignoreList, true)
            ), 1601484307);
        }

        return array_filter($components, fn($path) => !preg_match($ignorePattern, (string) $path));
    }

    /**
     * Searches recursively for component files in a directory
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
                $this->scanForComponents($componentPath, $ext)
            );
        }

        return $components;
    }

    /**
     * Converts glob pattern to regular expression.
     *
     * This function is borrowed/based on composer package nette/finder
     * https://github.com/nette/finder/
     */
    protected static function buildPattern(array $masks): ?string
    {
        $pattern = [];
        foreach ($masks as $mask) {
            $mask = rtrim(strtr($mask, '\\', '/'), '/');
            $prefix = '';
            if ($mask === '') {
                continue;
            } elseif ($mask === '*') {
                return null;
            } elseif ($mask[0] === '/') { // absolute fixing
                $mask = ltrim($mask, '/');
                $prefix = '(?<=^/)';
            }
            $pattern[] = $prefix . strtr(
                preg_quote($mask, '#'),
                ['\*\*' => '.*', '\*' => '[^/]*', '\?' => '[^/]', '\[\!' => '[^', '\[' => '[', '\]' => ']', '\-' => '-']
            );
        }
        return $pattern ? '#/(' . implode('|', $pattern) . ')$#Di' : null;
    }
}
