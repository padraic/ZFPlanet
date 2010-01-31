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
            $outline->setAttribute('type', $this->_getFeedType($blog->type));
            $outline->setAttribute('version', $this->_getFeedVersion($blog->type));
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
    
    protected function _getFeedVersion($type) {
        switch($type) {
            case 'RSS 2.0':
                return 'RSS2';
            case 'Atom 1.0':
                return 'ATOM1';
            case 'RSS 1.0':
                return 'RSS1';
            case 'Atom 0.3':
                return 'ATOM';
            default:
                return 'RSS';
        }
    }
    
    protected function _getFeedType($type) {
        if (preg_match("/atom/i", $type)) {
            return 'atom';
        }
        return 'rss';
    }

}

