// This code is a demo for running the module installer. The code below should be present in your main PHP module at the root directory.

public function install()
    {
        if (!parent::install()) {
            $this->_errors[] = $this->l('Unable to install module');
            return false;
        }

        $moduleInstaller = new \ModuleInstaller(
            [
                'tabClassName' => [
                    'AdminPickingList' => 'Picking List', 
                    'AdminPickingListConfiguration' => 'Picking List Configuration',
                    'AdminPickingListGenerator' => 'Picking List Generator',
                ],
                'hookName' => [
                    'moduleRoutes',
                    'displayNav1',
                    'actionFrontControllerInitBefore'
                ],
            ],
            $this->id
        );
        $moduleInstaller->processModuleInstallationOrUninstallation('install');
        return true;
    }

  public function uninstall()
    {
        $moduleInstaller = new \ModuleInstaller(
            [
                'tabClassName' => [
                    'AdminPickingList',
                    'AdminPickingListConfiguration',
                    'AdminPickingListGenerator',
                ],
                'hookName' => [
                    'moduleRoutes',
                    'displayNav1',
                    'actionFrontControllerInitBefore'
                ],
            ],
            $this->id
        );
        if ($moduleInstaller->processModuleInstallationOrUninstallation('uninstall')) {
            return parent::uninstall();
        } else {
            return false;
        }
    }
