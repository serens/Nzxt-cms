<?php
namespace Nzxt\Utility\Loader;

use Signature\Core\AutoloaderInterface;

/**
 * Class Loader.
 *
 * This utility traverses through each module's file structure and searches for Content classes which
 * can be selected as content for a node.
 * @package Nzxt\Utility\Loader
 */
class ContentLoader extends AbstractModuleBasedLoader
{
    /**
     * Tries to find classes which derive from \Nzxt\Model\Content\AbstractContent.
     * @param string $module If specified, only this module will be examined.
     * @return array
     */
    static public function findContentClasses($module = null): array
    {
        $foundContentClasses = [];

        foreach (array_keys(self::getModulesToExamine($module)) as $moduleName) {
            if (!array_key_exists($moduleName, $foundContentClasses)) {
                $foundContentClasses[$moduleName] = [];
            }

            $path = implode(DIRECTORY_SEPARATOR, [
                AutoloaderInterface::MODULES_PATHNAME,
                $moduleName,
                'src',
                'Model',
                'Content'
            ]);

            if (!is_dir($path)) {
                continue; // Only check modules which have a specific directory structure
            }

            $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            $phpFiles = new \RegexIterator($allFiles, '/\.php$/');

            /** @var \SplFileInfo $phpFile */
            foreach ($phpFiles as $phpFile) {
                // Try to determine a class name based on the found php file
                if ('' != ($class = self::resolveClassnameByFile($phpFile->getPathname()))) {
                    $foundContentClasses[$moduleName][] = $class;
                }
            }
        }

        return $foundContentClasses;
    }

    /**
     * @param string $classFilename
     * @return string
     */
    static protected function resolveClassnameByFile(string $classFilename): string
    {
        // Build a fully qualified classname based on the filename:
        $fileParts = explode(DIRECTORY_SEPARATOR, $classFilename);

        // Get rid of 'modules' and 'src':
        unset(
            $fileParts[array_search(AutoloaderInterface::MODULES_PATHNAME, $fileParts)],
            $fileParts[array_search('src', $fileParts)]
        );

        $fileParts = array_values($fileParts);

        // Get rid of classes which contain 'Abstract" in the last part
        if (false !== strpos($fileParts[count($fileParts) - 1], 'Abstract')) {
            return '';
        }

        $className = str_replace('.php', '', implode('\\', $fileParts));

        return class_exists($className) ? $className : '';
    }
}
