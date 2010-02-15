<?php

class Zfplanet_SearchController extends Zend_Controller_Action
{

    public function indexAction()
    {
        if (!isset($_POST['search'])) {
            $this->_redirect('/');
        }
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        try {
            $index = Zend_Search_Lucene::open($options['search']['indexPath']);
        } catch (Zend_Search_Lucene_Exception $e) {
            $index = Zend_Search_Lucene::create($options['search']['indexPath']);
        }
        try {
            $query = Zend_Search_Lucene_Search_QueryParser::parse($_POST['search'], 'utf-8');
            $hits = $index->find($_POST['search']);
        } catch (Zend_Search_Lucene_Exception $e) {
            $hits = array();
        }
        $this->view->results = $hits;
    }

}
