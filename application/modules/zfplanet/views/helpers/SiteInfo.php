<?php

class Zfplanet_View_Helper_SiteInfo extends Zend_View_Helper_Abstract
{

    protected $_data = array();

    public function __construct()
    {
        $this->_data = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getOptions();
    }

    public function siteInfo($name, $term = 'site')
    {
        if (isset($this->_data[$term]) && isset($this->_data[$term][$name])) {
            return $this->_data[$term][$name];
        }
        return null;
    }

}
