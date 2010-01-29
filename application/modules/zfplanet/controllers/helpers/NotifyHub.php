<?php

class Zfplanet_Controller_Action_Helper_NotifyHub
extends Zend_Controller_Action_Helper_Abstract
{

    public function direct(array $hubUris)
    {
        $publisher = new Zend_Feed_Pubsubhubbub_Publisher;
        $publisher->addHubUrls($hubUris);
        $publisher->addUpdatedTopicUrls(array(
            $this->_getUri('atom', 'feed', 'zfplanet'),
            $this->_getUri('rss', 'feed', 'zfplanet')
        ));
        $publisher->notifyAll();
        return $publisher;
    }
    
    protected function _getUri($action, $controller, $module)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            $host = Zend_Controller_Front::getInstance()
                ->getParam('bootstrap')
                ->getOption('host');
        }
        $uri = Zend_Uri::factory('http');
        $uri->setHost($host);
        $uri->setPath(
            Zend_Controller_Action_HelperBroker::getStaticHelper('Url')
                ->simple($action, $controller, $module)
        );
        return rtrim($uri->getUri(), '/');
    }

}
