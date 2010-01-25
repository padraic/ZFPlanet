<?php

class Zfplanet_IndexController extends Zend_Controller_Action
{

    protected $_paginator = null;

    public function init()
    {
        $this->_paginator = new Zend_Paginator(
            Doctrine_Core::getTable('Zfplanet_Model_Entry')
        );
        $this->view->paginator = $this->_paginator;
        $this->_helper->cache(array('index', 'page'), array('all-entries'));
    }

    public function indexAction()
    {
        $this->_paginator->setCurrentPageNumber(1);
    }
    
    public function pageAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_paginator->setCurrentPageNumber(
            $this->_getParam('pageNumber')
        );
        $this->render('index');
    }

}

