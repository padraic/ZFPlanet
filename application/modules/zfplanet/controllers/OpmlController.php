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
        $dom->formatOutput = true;
        $opml = $dom->createElement('opml');
        $opml->setAttribute('version', '2.0');
        $dom->appendChild($opml);
        $body = $dom->createElement('body');
        $blogs = $this->_getBlogs();
        foreach ($blogs as $blog) {
            $outline = $dom->createElement('outline');
            $outline->setAttribute('text', $blog->title);
            $outline->setAttribute('htmlUrl', $blog->Blog->uri);
            $outline->setAttribute('xmlUrl', $blog->uri);
            $body->appendChild($outline);
        }
        $opml->appendChild($body);
        $this->getResponse()
            ->setHeader('Content-type', 'application/xml; charset=UTF-8')
            ->setBody($dom->saveXML());
    }
    
    protected function _getBlogs($count = null)
    {
        $q = Doctrine_Query::create()
            ->from('Zfplanet_Model_Feed')
            ->groupBy('title')
            ->orderBy('title ASC');
        if ($count) {
            $q->limit($count);  
        }
        $bloggers = $q->execute();
        return $bloggers;
    }

}

