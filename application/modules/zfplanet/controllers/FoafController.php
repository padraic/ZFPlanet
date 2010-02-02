<?php

class Zfplanet_FoafController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        //$this->_helper->cache(array('index'), array('allblogs'), 'xml');
    }
    
    public function indexAction()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $rdf = $dom->createElement('rdf:RDF');
        $rdf->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $rdf->setAttribute('xmlns:rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
        $rdf->setAttribute('xmlns:foaf', 'http://xmlns.com/foaf/0.1/');
        $rdf->setAttribute('xmlns:rss', 'http://purl.org/rss/1.0/');
        $rdf->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $dom->appendChild($rdf);
        $foaf = $dom->createElement('foaf:Group');
        $rdf->appendChild($foaf);
        $name = $dom->createElement('foaf:name');
        $text = $dom->createTextNode($this->_getSiteTitle());
        $name->appendChild($text);
        $home = $dom->createElement('foaf:homepage');
        $text = $dom->createTextNode($this->_getBaseUri());
        $home->appendChild($text);
        $seeAlso = $dom->createElement('rdfs:seeAlso');
        $seeAlso->setAttribute('rdf:resource', $this->_getBaseUri() . 'foaf');
        $foaf->appendChild($name);
        $foaf->appendChild($home);
        $foaf->appendChild($seeAlso);
        $blogs = $this->_getBlogs();
        foreach ($blogs as $blog) {
            $member = $dom->createElement('foaf:member');
            $foaf->appendChild($member);
            $agent = $dom->createElement('foaf:Agent');
            $member->appendChild($agent);
            $name = $dom->createElement('foaf:name');
            $agent->appendChild($name);
            $weblog = $dom->createElement('foaf:weblog');
            $agent->appendChild($weblog);
            $document = $dom->createElement('foaf:Document');
            $document->setAttribute('rdf:about', $blog->Blog->uri);
            $weblog->appendChild($document);
            $title = $dom->createElement('dc:title');
            $text = $dom->createTextNode($blog->title);
            $title->appendChild($text);
            $document->appendChild($title);
            $docSeeAlso = $dom->createElement('rdfs:seeAlso');
            $document->appendChild($docSeeAlso);
            $channel = $dom->createElement('rss:channel');
            $channel->setAttribute('rdf:about', $blog->uri);
            $docSeeAlso->appendChild($channel);
        }
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
    
    protected function _getBaseUri()
    {
        $uri = Zend_Uri::factory('http');
        $uri->setHost($_SERVER['HTTP_HOST']);
        return rtrim($uri->getUri(), '/') . '/';
    }
    
    protected function _getSiteTitle()
    {
        $site = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getOption('site');
        return $site['title'];
    }
    

}

