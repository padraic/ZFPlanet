<?php

class Zfplanet_Controller_Action_Helper_NotifyHub
extends Zend_Controller_Action_Helper_Abstract
{

    public function direct(array $hubUris)
    {
        foreach ($hubUris as $hubUri) {
            if (!Zend_Uri::check($hubUri)) {
                throw new Exception('Invalid Hub Endpoint: ' . $hubUri);
            }
        }
        $publisher = new Zend_Feed_Pubsubhubbub_Publisher;
        $publisher->addHubUrls($hubUris);
        $publisher->addTopicUrls(array(
            $this->_getUri('atom', 'feed', 'zfplanet'),
            $this->_getUri('rss', 'feed', 'zfplanet')
        ));
        $publisher->notifyAll();
        return $publisher;
    }
    
    protected function _getUri($action, $controller, $module)
    {
        if ($_SERVER['HTTP_HOST']) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            $host = Zend_Controller_Front::getInstance()
                ->getInvokeArg('bootstrap')
                ->getOption('host');
        }
        $uri = Zend_Uri::factory('http');
        $uri->setHost($host);
        $uri->setPath(
            $this->_helper->getHelper('Url')->simple($action, $controller, $module)
        );
        return rtrim($uri->getUri(), '/');
    }

}
