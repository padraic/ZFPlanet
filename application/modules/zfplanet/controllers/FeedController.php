<?php
/**
 * This is very untidy, so to explain... We've enabled static file caching here
 * so the feeds are saved as actual XML files served directly by Apache (see
 * .htaccess). Apache should handle conditional GETs out of the box.
 * The intention is to make this behaviour configurable (not done yet). If static
 * caching disabled, the code below will always dynamically generate the feed,
 * AND handle conditional GET requests also (saving us from actually generating
 * the feed all the time - costing a single DB query to grab the latest
 * dateModified value).
 */
class Zfplanet_FeedController extends Zend_Controller_Action
{

    protected $_feed = null;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->cache(array('atom', 'rss'), array('allentries'), 'xml');
        $lastSyncedEntry = Doctrine_Query::create()
            ->select('dateModified')
            ->from('Zfplanet_Model_Entry')
            ->orderBy('dateModified DESC')
            ->fetchone();
        // todo: check if this was successful in case no entries yet
        $lastSyncDate = new Zend_Date(
            $lastSyncedEntry->dateModified,
            Zend_Date::ISO_8601
        );
        $lastModified = $lastSyncDate->get(Zend_Date::RFC_1123);
        if (!$this->_handleConditionalGet($lastModified)) {
            $this->_feed = $this->_generateFeedContainer();
            $this->getResponse()
                ->setHeader('Last-Modified', $lastModified)
                ->setHeader('ETag', '"' . md5($lastModified) . '"')
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        }
    }
    
    public function atomAction()
    {
        if (!$this->_feed) return;
        $this->getResponse()
            ->setHeader('Content-type', 'application/atom+xml; charset=UTF-8')
            ->setBody($this->_feed->export('atom'));
    }
    
    public function rssAction()
    {
        if (!$this->_feed) return;
        $this->getResponse()
            ->setHeader('Content-type', 'application/rss+xml; charset=UTF-8')
            ->setBody($this->_feed->export('rss'));
    }
    
    protected function _generateFeedContainer()
    {
        $entries = Doctrine_Query::create()
            ->from('Zfplanet_Model_Entry')
            ->orderBy('publishedDate DESC')
            ->limit(20)
            ->execute();
        $now = new Zend_Date;
        $feed = new Zend_Feed_Writer_Feed;
         // TODO: Extract site info to config file and detect feed URLs automatically from HOST
        $feed->setTitle('ZF Planet');
        $feed->setDescription('Zend Framework Blog Planet');
        $feed->setDateModified($now);
        $feed->setLink('http://zfplanet');
        $feed->setFeedLink('http://zfplanet/feed/atom', 'atom');
        $feed->setFeedLink('http://zfplanet/feed/rss', 'rss');
        $feed->addHubs(array(
            'http://pubsubhubbub.appspot.com/'
        ));
        foreach ($entries as $data) {
            $entry = $feed->createEntry();
            $entry->setTitle($data->title);
            $entry->addAuthor(array(
                'name' => $data->author
            ));
            if (!empty($data->description)) {
                $entry->setDescription($data->description);
            }
            $entry->setLink($data->uri);
            $entry->setContent($data->content);
            $publishedDate = new Zend_Date($data->publishedDate, Zend_Date::ISO_8601);
            $entry->setDateCreated($publishedDate);
            $updatedDate = new Zend_Date($data->updatedDate, Zend_Date::ISO_8601);
            $entry->setDateModified($updatedDate);
            $feed->addEntry($entry);
        }
        return $feed;
    }
    
    protected function _handleConditionalGet($lastModified)
    {
        $match = false;
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
        && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastModified) {
            $match = true;
        }
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])
        && $_SERVER['HTTP_IF_NONE_MATCH'] == md5($lastModified)) {
            $match = true;
        }
        if (!$match) {
            return false;
        }
        $this->getResponse()->setHttpResponseCode(304);
        return true;
    }

}

