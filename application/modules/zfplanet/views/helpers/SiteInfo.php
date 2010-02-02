<?php

class Zfplanet_View_Helper_SiteInfo extends Zend_View_Helper_Abstract
{

    protected $_data = array();

    public function __construct()
    {
        $this->_data = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getOption('site');
    }

    public function siteInfo($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        return null;
    }

}
