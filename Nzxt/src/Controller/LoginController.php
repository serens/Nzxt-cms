<?php
namespace Nzxt\Controller;

use Signature\Html\Form\Element\Input;
use Signature\Html\Form\Element\Password;
use Signature\Html\Form\Form;

/**
 * Class LoginController
 * @package Nzxt\Controller
 */
class LoginController extends AbstractBackendController
{
    protected $requireAuthentication = false;

    /**
     * Set a layout-template for the view.
     * @return void
     */
    protected function initView()
    {
        parent::initView();

        $this->view->setLayout($this->getTemplateDir() . '/Layouts/Backend/Login.phtml');
    }

    /**
     * @return void
     */
    public function loginAction()
    {
        $form = new Form($this->request, [], [new Input('username'), new Password('password')]);
        $authFailed = false;

        if ($this->request->isPost()) {
            $credentials = [
                'username' => $form->getElementValue('username'),
                'password' => $form->getElementValue('password'),
            ];

            if ($user = $this->authService->authenticate($credentials)) {
                $user
                    ->setFieldValue('last_login', date('Y-m-d H:i:s'))
                    ->save();

                $this->authService->setCurrentUser($user);

                if (array_key_exists('redirect', $_SESSION)) {
                    $redirect = $_SESSION['redirect'];
                    unset($_SESSION['redirect']);
                } else {
                    $redirect = $this->linkBuilder->build('node:view', [
                        '#node' => $this->configurationService->getConfigByPath('Nzxt', 'RootNodeId')]
                    );
                }

                $this->redirect($redirect);
            } else {
                $authFailed = true;
            }
        }

        $this->view->setViewData([
            'authenfication_failed' => $authFailed,
            'form' => $form,
        ]);
    }

    /**
     * @return void
     */
    public function logoutAction()
    {
        $this->authService->setCurrentUser(null);
        $this->redirect($this->linkBuilder->build('auth:login'));
    }
}
