<?php

class Zfplanet_Model_Service_TwitterNotifier
{

    protected $_bclient = null;
    
    protected $_tclient = null;
    
    protected $_enabled = false;
    
    public function __construct(array $config, Zend_Cache_Core $cache)
    {
        if (!($accessToken = $cache->load('access-token'))) {
            return;
        }
        $this->_tclient = $accessToken->getHttpClient($config['twitter']);
        $this->_tclient->setConfig(array('keepalive'=>true));
        $this->_tclient->setUri('http://twitter.com/statuses/update.json');
        $this->_tclient->setMethod(Zend_Http_Client::POST);
        $this->_bclient = new Zend_Http_Client(array('keepalive'=>true));
        $this->_bclient->setUri('http://api.bit.ly/shorten');
        $this->_bclient->setMethod(Zend_Http_Client::GET);
        $this->_bclient->setParameterGet('version', '2.0.1');
        $this->_bclient->setParameterGet('login', $config['bitly']['login']);
        $this->_bclient->setParameterGet('apiKey', $config['bitly']['apiKey']);
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
    
    public function isEnabled()
    {
        return $this->_enabled;
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
