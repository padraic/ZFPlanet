<?php

class Zfplanet_View_Helper_BaseUri extends Zend_View_Helper_Abstract
{

    public function baseUri()
    {
        $uri = Zend_Uri::factory('http');
        $uri->setHost($_SERVER['HTTP_HOST']);
        return rtrim($uri->getUri(), '/') . '/';
    }

}
