<?php

class Zfplanet_IndexController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $entries = Doctrine_Query::create()
            ->from('Zfplanet_Model_Entry')
            ->orderBy('publishedDate DESC')
            ->limit(10)
            ->execute();
        $this->view->entries = $entries;
    }

}

