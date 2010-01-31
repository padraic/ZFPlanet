<?php

class Zfplanet_OpmlController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        //$this->_helper->cache(array('opml'), array('allblogs'), 'xml');
    }
    
    public function indexAction()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $opml = $dom->createElement('opml');
        $opml->setAttribute('version', '2.0');
        $dom->appendChild($opml);
        $this->getResponse()
            ->setHeader('Content-type', 'application/opml+xml; charset=UTF-8')
            ->setBody($dom->saveXML());
    }

}

