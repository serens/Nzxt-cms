<?php
namespace Nzxt\Controller;

/**
 * Class InstallController
 * @package Nzxt\Controller
 */
class InstallController extends AbstractBackendController
{
    protected $requireAuthentication = false;

    /**
     * Set a layout-template for the view.
     * @return void
     */
    protected function initView()
    {
        parent::initView();

        $this->view->setLayout($this->getTemplateDir() . '/Layouts/Backend/Install.phtml');
    }

    /**
     * @return void
     */
    public function installAction()
    {
        return 'Install....';

        $this->view->setViewData([
            'authenfication_failed' => $authFailed,
            'form' => $form,
        ]);
    }
}
