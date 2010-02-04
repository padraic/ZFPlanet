<?php

class Admin_TwitterController extends Zend_Controller_Action
{

    protected $_consumer = null;
    
    protected $_cache = null;

    public function init()
    {
        $options = $this->getInvokeArg('bootstrap')->getOption('twitter_oauth');
        $options['callbackUrl'] = $this->_getCallbackUri();
        // workaround since INI config cannot define a NULL value :(
        $this->_helper->getHelper('Cache')->setTemplateOptions(
            'twitter',
            array('frontend'=>array('options'=>array('lifetime'=>null)))
        );
        $this->_cache = $this->_helper->getHelper('Cache')->getCache('twitter');
        $this->_consumer = new Zend_Oauth_Consumer($options);
    }

    public function associateAction()
    {
        if (!$this->_cache->load('access-token')) {
            $requestToken = $this->_consumer->getRequestToken();
            $this->_cache->save($requestToken, 'request-token');
            $redirect = $this->_consumer->getRedirectUrl();
            $this->_redirect($redirectUrl);
        }
    }
    
    public function callbackAction()
    {
        if (!empty($_GET) && ($requestToken = $this->_cache->load('request-token'))) {
            $accessToken = $this->_consumer->getAccessToken($_GET, $requestToken);
            $this->_cache->remove('request-token');
            $this->_cache->save($accessToken, 'access-token');
        } else {
            $this->_redirect('/');
        }
    }
    
    protected function _getCallbackUri()
    {
        $uri = Zend_Uri::factory('http');
        $uri->setHost($_SERVER['HTTP_HOST']);
        $uri->setPath(
            $this->_helper->getHelper('Url')->simple('twitter', 'callback', 'admin')
        );
        return rtrim($uri->getUri(), '/');
    }

}
