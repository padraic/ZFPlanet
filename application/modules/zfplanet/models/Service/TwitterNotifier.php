<?php

class Zfplanet_Model_Service_TwitterNotifier
{

    protected $_bclient = null;
    
    protected $_tclient = null;
    
    protected $_enabled = false;
    
    protected $_encoding = 'UTF-8';
    
    /**
     * Constructor
     *
     * The config array requires two keys, "twitter" and (optionally) "bitly",
     * each containing the necessary values as shown below.
     */
    public function __construct(array $config, Zend_Cache_Core $cache)
    {
        $accessToken = $cache->load('accesstoken');
        if (!$accessToken) {
            return;
        }
        if (isset($config['twitter']['encoding'])) $this->_encoding = $config['twitter']['encoding'];
        $this->_enabled = true;
        $this->_tclient = $accessToken->getHttpClient($config['twitter']);
        $this->_tclient->setConfig(array('keepalive'=>true));
        $this->_tclient->setUri('http://twitter.com/statuses/update.json');
        $this->_tclient->setMethod(Zend_Http_Client::POST);
        if (isset($config['bitly'])) {
            $this->_bclient = new Zend_Http_Client(null, array('keepalive'=>true));
            $this->_bclient->setUri('http://api.bit.ly/shorten');
            $this->_bclient->setMethod(Zend_Http_Client::GET);
            $this->_bclient->setParameterGet('version', '2.0.1');
            $this->_bclient->setParameterGet('login', $config['bitly']['login']);
            $this->_bclient->setParameterGet('apiKey', $config['bitly']['apiKey']);
        }
    }
    
    public function notify(Zfplanet_Model_Entry $entry)
    {
        if (!$this->_enabled) {
            return;
        }
        $tlink = $this->_shortenLink($entry->uri);
        $status = $entry->title
            . ' (by '
            . $entry->author
            .') '
            . $tlink
            . ' ';
        $content = html_entity_decode(strip_tags($entry->content), ENT_QUOTES, $this->_encoding);
        $appendContent = substr($content, 0, (136 - strlen($status)));
        $status .= $appendContent . '...';
        $this->_tclient->setParameterPost('status', $status);
        $response = $this->_tclient->request();
        if ($response->getStatus() == 304) {
            throw new Exception('Twitter API limits exceeded');
        } elseif ($response->getStatus() !== 200) {
            throw new Exception('Twitter API Error - Status ' . $response->getStatus());
        }
    }
    
    public function isEnabled()
    {
        return $this->_enabled;
    }
    
    protected function _shortenLink($uri)
    {
        if (is_null($this->_bclient)) return $uri;
        $json = $this->_bclient
            ->setParameterGet('longUrl', $uri)
            ->request()->getBody();
        $result = Zend_Json::decode($json);
        if ($result['errorCode'] > 0) {
            return $uri;
        }
        return $result['results'][$uri]['shortUrl'];
    }
}
