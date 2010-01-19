<?php

class ZFExt_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if ($this->getRequest()->getParam('module') !== 'admin') {
            return;
        }
        if (!Zend_Auth::getInstance()->hasIdentity() && (
        $this->getRequest()->getParam('controller') !== 'user'
        && $this->getRequest()->getParam('action') !== 'login'
        )) {
            $r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $r->setCode(303)->gotoSimple('login', 'user', 'admin');
        }
    }

}
