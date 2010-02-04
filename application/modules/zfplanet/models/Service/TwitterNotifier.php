<?php

class Zfplanet_Model_Service_TwitterNotifier
{

    protected $_bclient = null;
    
    protected $_tclient = null;
    
    protected $_enabled = false;
    
    public function __construct()
    {
        $tcache = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getResource('Cachemanager')
            ->getCache('twitter');
        if (!($accessToken = $tcache->load('access-token'))) {
            return;
        }
        $config = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getOption('twitter_oauth');
        $this->_tclient = $accessToken->getHttpClient($config);
        $this->_tclient->setConfig(array('keepalive'=>true));
        $this->_tclient->setUri('http://twitter.com/statuses/update.json');
        $this->_tclient->setMethod(Zend_Http_Client::POST);
        $config = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getOption('bitly');
        $this->_bclient = new Zend_Http_Client(array('keepalive'=>true));
        $this->_bclient->setUri('http://api.bit.ly/shorten');
        $this->_bclient->setMethod(Zend_Http_Client::GET);
        $this->_bclient->setParameterGet('version', '2.0.1');
        $this->_bclient->setParameterGet('login', $config['login']);
        $this->_bclient->setParameterGet('apiKey', $config['apiKey']);
    }
    
    public function notify(Zfplanet_Model_Entry $entry)
    {
        if (!$this->_enabled) {
            return;
        }
        $tlink = $this->_shortenLink($entry->uri);
        $this->_tclient->setParameterPost(
            'status',
            substr($entry->title, 0, (139-strlen($tlink))) . ' ' . $tlink
        );
        $this->_tclient->request();
    }
    
    protected function _shortenLink($uri)
    {
        $json = $this->_bclient
            ->setParameterGet('longUrl', $uri)
            ->request();
        $result = Zend_Json::decode($json);
        if ($result['errorCode']) {
            return $uri;
        }
        return $result['results'][$uri]['shortUrl'];
    }
}
