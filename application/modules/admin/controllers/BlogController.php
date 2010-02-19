<?php

class Admin_BlogController extends Zend_Controller_Action
{

    public function createAction()
    {
        $form = new Admin_Form_AddBlog;
        $this->view->addBlogForm = $form;
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        if ($flashMessenger->hasMessages()) {
            $this->view->messages = $flashMessenger->getMessages();
        }
    }
    
    public function deleteAction()
    {
        $form = new Admin_Form_DeleteBlog;
        $this->view->deleteBlogForm = $form;
        $this->_addBlogsToForm($form);
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        if ($flashMessenger->hasMessages()) {
            $this->view->messages = $flashMessenger->getMessages();
        }
    }
    
    public function processAction()
    {
        $form = new Admin_Form_AddBlog;
        if (!$this->getRequest()->isPost()) {
            return $this->_forward('admin/index');
        }
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        if (!$form->isValid($_POST)) {
            $flashMessenger->addMessage('Form data invalid: recheck details and try again.');
            $flashMessenger->addMessage('error');
            $this->_redirect('/admin/blog/create');
        }
        $values = $form->getValues();

        $blog = new Zfplanet_Model_Blog;
        $blog->contactName = $values['contactName'];
        if (isset($values['contactEmail'])) {
            $blog->contactEmail = $values['contactEmail'];
        }
        $blog->uri = $values['uri'];
        try {
            $data = Zend_Feed_Reader::import($values['feedUri']);
        } catch (Exception $e) {
            $flashMessenger->addMessage('Problem fetching feed: ' . $e->getMessage());
            $flashMessenger->addMessage('error');
            $this->_redirect('/admin/blog/create');
        }
        $blog->feedId = $data->getId();
        $feed = new Zfplanet_Model_Feed;
        $feedMeta = new Zfplanet_Model_FeedMeta;
        $feed->id = $data->getId();
        $feed->uri = $data->getFeedLink();
        if (isset($feed->uri)) {
            $feed->uri = $values['feedUri'];
        }
        $feed->blogId = $blog->id;
        $feed->title = Zfplanet_Model_Feed::getHtmlPurifier()->purify($data->getTitle());
        $feed->type = $this->_getFeedVersion($data->getType());
        $feed->isActive = 1;
        $feedMeta->feedId = $data->getId();
        $feedMeta->title = $data->getTitle();
        $feedMeta->description = $data->getDescription();
        $feedMeta->link = $data->getLink();
        $feedMeta->feedLink = $data->getFeedLink();
        $blog->save();
        $feed->save();
        $feedMeta->save();
        $this->_checkPubsubEnabled($data);
        $flashMessenger->addMessage('Blog successfully added!');
        $flashMessenger->addMessage('success');
        $this->_redirect('/admin/blog/create');
    }
    
    public function process2Action()
    {
        $form = new Admin_Form_DeleteBlog;
        $this->_addBlogsToForm($form);
        if (!$this->getRequest()->isPost()) {
            return $this->_forward('admin/index');
        }
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        if (!$form->isValid($_POST)) {
            $flashMessenger->addMessage('Form data invalid: recheck details and try again.');
            $flashMessenger->addMessage('error');
            $this->_redirect('/admin/blog/delete');
        }
        $values = $form->getValues();
        // time to unsubscribe any blogs that were PuSH enabled
        $feeds = Doctrine_Query::create()
            ->from('Zfplanet_Model_Feed')
            ->wherein('blogid', $values['feeds'])
            ->execute();
        if ($feeds) $this->_checkPubsubDisabled($feeds);
        // This is short because cascade deletes are enabled in Models (onDelete: CASCADE)
        // so yes, this will delete all related feeds/entries too ;)
        $query = Doctrine_Query::create()
            ->delete('Zfplanet_Model_Blog')
            ->whereIn('id', $values['feeds'])
            ->execute();
        $flashMessenger->addMessage('Blogs successfully deleted!');
        $flashMessenger->addMessage('success');
        $this->_redirect('/admin/blog/delete');
    }
    
    protected function _checkPubsubEnabled(Zend_Feed_Reader_FeedAbstract $feed)
    {
        if (!$feed->getHubs()) {
            return;
        }
        $hubs = $feed->getHubs();
        $sub = new Zend_Feed_Pubsubhubbub_Subscriber;
        $sub->setStorage(Doctrine_Core::getTable('Zfplanet_Model_Subscription'));
        $sub->addHubUrls($hubs);
        $sub->setTopicUrl($feed->getFeedLink());
        $sub->usePathParameter();
        $sub->setCallbackUrl(
            $this->_getCallbackUri()
        );
        $sub->subscribeAll();
    }
    
    protected function _checkPubsubDisabled(array $feeds)
    {
        $feedUris = array();
        foreach ($feeds as $feed) $feedUris[] = $feed->uri;
        $subs = Doctrine_Query::create()
            ->from('Zfplanet_Model_Subscription')
            ->whereIn('topic_url', $feedUris)
            ->execute();
        $sub = new Zend_Feed_Pubsubhubbub_Subscriber;
        $sub->setStorage(Doctrine_Core::getTable('Zfplanet_Model_Subscription'));
        $sub->usePathParameter();
        $sub->setCallbackUrl(
            $this->_getCallbackUri()
        );
        foreach($subs as $subscription) {
            foreach($sub->getHubUrls() as $url) $sub->removeHubUrl($url); //reset
            $sub->addHubUrl($sub->hub_url);
            $sub->setTopicUrl($sub->topic_url);
            $sub->unsubscribeAll();
        }
    }
    
    protected function _getCallbackUri()
    {
        $uri = Zend_Uri::factory('http');
        $uri->setHost($_SERVER['HTTP_HOST']);
        $uri->setPath(
            $this->_helper->getHelper('Url')->simple(null, 'callback', 'zfplanet')
        );
        return rtrim($uri->getUri(), '/');
    }
    
    protected function _getFeedVersion($type) {
        switch($type) {
            case Zend_Feed_Reader::TYPE_RSS_20:
                return 'RSS 2.0';
            case Zend_Feed_Reader::TYPE_ATOM_10:
                return 'Atom 1.0';
            case Zend_Feed_Reader::TYPE_RSS_10:
                return 'RSS 1.0';
            case Zend_Feed_Reader::TYPE_ATOM_03:
                return 'Atom 0.3';
            default:
                return 'RSS';
        }
    }
    
    protected function _addBlogsToForm($form)
    {
        $feeds = Doctrine_Query::create()
            ->from('Zfplanet_Model_Feed')
            ->orderBy('title ASC')
            ->execute();
        $this->view->feedCount = count($feeds);
        $checkbox = $form->getElement('feeds');
        foreach ($feeds as $feed) {
            $checkbox->addMultiOption($feed->Blog->id, $feed->title);
        }
    }

}
