<?php
namespace Nzxt\Utility\Loader;

use Signature\Core\AutoloaderInterface;

/**
 * Class Loader.
 *
 * This utility traverses through each module's file structure and searches for Templates which
 * can be selected by a page.
 * @package Nzxt\Utility\Loader
 */
class TemplateLoader extends AbstractModuleBasedLoader
{
    /**
     * Tries to find templates which exist in a specific directory structure.
     * @param string $module If specified, only this module will be examined.
     * @return array
     */
    static public function findTemplates($module = null): array
    {
        $foundTemplates = [];

        foreach (array_keys(self::getModulesToExamine($module)) as $moduleName) {
            $path = implode(DIRECTORY_SEPARATOR, [
                AutoloaderInterface::MODULES_PATHNAME,
                $moduleName,
                'tpl',
                'Page',
            ]);

            if (!is_dir($path)) {
                continue; // Only check modules which have a specific directory structure
            }

            $allFiles   = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            $phtmlFiles = new \RegexIterator($allFiles, '/\.phtml$/');

            if (0 === iterator_count($phtmlFiles)) {
                continue; // Get rid of module if it does not provide any templates
            }

            // Each module will have its own set of templates. The template-list will become a <optgroup> then.
            if (!array_key_exists($moduleName, $foundTemplates)) {
                $foundTemplates[$moduleName] = [];
            }

            /** @var \SplFileInfo $template */
            foreach ($phtmlFiles as $template) {
                $foundTemplates[$moduleName][$template->getPathname()] = $template->getFilename();
            }
        }

        if (0 === count($foundTemplates)) {
            $foundTemplates = [
                '' => '(No templates found)'
            ];
        }

        return $foundTemplates;
    }
}
