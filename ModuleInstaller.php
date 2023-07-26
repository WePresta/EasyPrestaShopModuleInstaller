<?php
/**
 * @author    WePresta <mail@wepresta.shop>
 * @copyright Since 2023 www.wepresta.shop
 */

class ModuleInstaller
{
    protected $tabs;
    protected $module;
    protected $hookName;

    public function __construct(array $moduleDefinition)
    {
        $this->tabs = $moduleDefinition['tabClassName'];
        $this->module = \Module::getInstanceByName('YOUR MODULE NAME');
        $this->hookName = $moduleDefinition['hookName'];
    }

    /**
     * Process the installation or uninstallation of a module.
     *	
     * @param string $type the type of process to perform ('install' or 'uninstall')
     */
    public function processModuleInstallationOrUninstallation(string $type)
    {
        // Check the type of process to perform.
        switch ($type) {
            // If installing the module:
            case 'install':
                // Manage database tables related to the module.
                $this->manageTables($type);
                // Install tabs related to the module.
                $this->installTabs();
                // Install Hooks related to the module.
                $this->installHooks();
                break;
                // If uninstalling the module:
            case 'uninstall':
                // Manage database tables related to the module.
                $this->manageTables($type);
                // Uninstall tabs related to the module.
                $this->uninstallTabs();
                // Uninstall Hooks related to the module.
                $this->uninstallHooks();
                break;
        }

        return true;
    }

    /**
     * Install tabs related to the module.
     *
     * @return bool whether the installation was successful
     */
    private function installTabs()
    {
        // Create main tab
        $mainTab = $this->tabs;
        reset($mainTab);
        $mainTabClassName = key($this->tabs);
        $mainTabName = current($this->tabs);
        $mainTab = $this->createNewTab($mainTabClassName, $mainTabName, \Tab::getIdFromClassName('DEFAULT'), $this->module->name);

        // Create subtabs
        if (count($this->tabs) > 1) {
            array_shift($this->tabs);
            $idMainTab = $mainTab->id;
            $this->createSubTabs($this->tabs, $idMainTab); // Create the subtabs.
        }

        return true; // Return success status.
    }

    /**
     * Create sub-tabs based on the provided array of tab data.
     *
     * @param array $subTabs
     * @param int $idMainTab
     */
    private function createSubTabs(array $subTabs, int $idMainTab)
    {
        // Loop through each tab data item:
        foreach ($subTabs as $className => $tabName) {
            // Create a sub tab with the provided data.
            $this->createNewTab($className, $tabName, $idMainTab);
        }
    }

    /**
     * Create a new tab in the PrestaShop back office menu.
     *
     * @param mixed $className the name of the class for the new tab
     * @param mixed $tabName the name of the new tab
     * @param mixed $parentId the ID of the parent tab
     *
     * @return \Tab|false the newly created tab object or false if the save operation fails
     */
    private function createNewTab($className, $tabName, $parentId)
    {
        // Get all languages supported by the shop.
        $languages = \Language::getLanguages();

        // Create a new tab object with the provided data.
        $tab = new \Tab();
        $tab->class_name = $className;
        $tab->module = $this->module->name;
        $tab->id_parent = (int) $parentId;

        // Set the name of the new tab for each language.
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }

        // Attempt to save the new tab to the database.
        if ($tab->save()) {
            return $tab; // Return the new tab object.
        } else {
            // If the save operation fails, log an error message.
            throw new \Exception("Can\'t install tab name : " . $tab->class_name);
        }
    }

    /**
     * Manage SQL tables
     *
     * @param string $type Type of SQL file to execute ('install' or 'uninstall')
     */
    private function manageTables(string $type)
    {
        // Get the SQL file content
        $sql = \Tools::file_get_contents(_PS_MODULE_DIR_ . '/' . $this->module->name . '/sql/' . $type . '.sql');
        // Replace prefixes and engine types in the SQL file content
        $sql = str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);
        // Execute the SQL query
        if ($sql) {
            $sql = preg_split("/;\s*[\r\n]+/", trim($sql));
            foreach ($sql as $query) {
                if (!\Db::getInstance()->execute(trim($query))) {
                    throw new \Exception("Can\'t install SQL");
                }
            }

            return true;
        }
    }

    /** Uninstall module tab */
    private function uninstallTabs()
    {
        // Loop through all tab class names and remove them
        foreach ($this->tabs as $tabClassName) {
            $idTab = (int) \Tab::getIdFromClassName($tabClassName);
            if ($idTab) {
                $tab = new \Tab($idTab);
                // Save the tab to remove it
                if (!$tab->save()) {
                    // Log the error message if the tab can't be removed
                    throw new \Exception('Can\'t uninstall tab ' . $tab->class_name);
                }
            }
        }

        return true;
    }

    /**
     * Installs hooks for the module.
     *
     * @throws \Exception if unable to install a hook
     */
    private function installHooks()
    {
        foreach ($this->hookName as $hook) {
            if (!\Hook::registerHook($this->module, $hook)) {
                throw new \Exception('Can\'t install hook ' . $hook);
            }
        }
    }

    /**
     * Uninstalls hooks for the module.
     *
     * @throws \Exception if unable to uninstall a hook
     */
    private function uninstallHooks()
    {
        foreach ($this->hookName as $hook) {
            if (!\Hook::unregisterHook($this->module, $hook)) {
                throw new \Exception('Can\'t uninstall hook ' . $hook);
            }
        }
    }
}
