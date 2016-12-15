<?php
namespace Nzxt\Utility\Loader;

use Signature\Object\ObjectProviderService;

/**
 * Class AbstractModuleBasedLoader.
 *
 * @package Nzxt\Utility\Loader
 */
abstract class AbstractModuleBasedLoader
{
    /**
     * Returns an array with module names which will be examined.
     * @param string $module
     * @return array
     * @throws \InvalidArgumentException If given module name is invalid.
     */
    static protected function getModulesToExamine(string $module = null): array
    {
        /** @var \Signature\Module\ModuleService $moduleService */
        $moduleService = ObjectProviderService::getInstance()->getService('ModuleService');
        $registeredModules = $moduleService->getRegisteredModules();
        $modulesToExamine  = [];

        if (null !== $module) {
            if (array_key_exists($module, $registeredModules)) {
                $modulesToExamine[$module] = $registeredModules[$module];
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Given module "%s" is not registered. Registered modules are: %s',
                    $module,
                    implode(', ', array_keys($registeredModules))
                ));
            }
        } else {
            $modulesToExamine = $registeredModules;
        }

        // Signature itself is a module but will never contain any content classes so we can avoid this module.
        if (array_key_exists('Signature', $modulesToExamine)) {
            unset($modulesToExamine['Signature']);
        }

        return $modulesToExamine;
    }
}
