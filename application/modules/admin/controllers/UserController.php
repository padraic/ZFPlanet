<?php

class Admin_UserController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            if ($this->getRequest()->getActionName() !== 'logout') {
                $this->_redirect('/admin');
            }
        } else {
            if ($this->getRequest()->getActionName() == 'logout') {
                $this->_redirect('/admin');
            }
        }
    }

    public function loginAction()
    {
        $this->view->userLoginForm = new Admin_Form_UserLogin;
    }
    
    public function processAction()
    {
        $form = new Admin_Form_UserLogin;
        $this->view->success = true;
        if (!$this->getRequest()->isPost()) {
            return $this->_forward('admin/user/login');
        }
        if (!$form->isValid($_POST)) {
            $this->view->success = false;
            $this->view->userLoginForm = $form;
        }
        $values = $form->getValues();
        $authAdapter = new ZFExt_Auth_Adapter_Doctrine(
            'Zfplanet_Model_User',
            'name',
            'password',
            'SHA256'
        );
        $authAdapter->setIdentity($values['name'])
            ->setCredential($values['password']);
        $auth = Zend_Auth::getInstance();
        $result  = $auth->authenticate($authAdapter);
        if (!$result->isValid()) {
            $this->view->userLoginForm = $form;
            $form->setDescription('Could not authenticate with given credentials.');
            $this->_helper->viewRenderer->setNoRender();
            $this->render('login');
            return;
        }
        Zend_Session::regenerateId();
        $this->_redirect('/admin');
    }
    
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/');
    }

}
