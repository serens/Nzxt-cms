<?php
/**
 * This file is part of the Signature MVC-Framework.
 * @copyright Sven Erens <sven@signature-framework.com>
 */

namespace Application\Controller;

use Signature\Mvc\Controller\ActionController;

/**
 * Class IndexController
 * @package Application\Controller
 */
class IndexController extends ActionController
{
    /**
     * Set a layout-template for the view.
     * @return void
     */
    protected function initView()
    {
        parent::initView();

        $this->view->setLayout('modules/Signature/tpl/Layouts/Default.phtml');
    }

    /**
     * Indexaction for this controller.
     * @return void
     */
    public function indexAction()
    {

    }
}
