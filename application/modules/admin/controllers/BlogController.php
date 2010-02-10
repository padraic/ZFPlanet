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
    
    public function editAction()
    {

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
        $blog->save();
        $feed = new Zfplanet_Model_Feed;
        $feed->id = $data->getId();
        $feed->uri = $data->getFeedLink();
        if (isset($feed->uri)) {
            $feed->uri = $values['feedUri'];
        }
        $feed->blogId = $blog->id;
        $feed->title = Zfplanet_Model_Feed::getHtmlPurifier()->purify($data->getTitle());
        $feed->type = $this->_getFeedVersion($data->getType());
        $feed->isActive = 1;
        $feed->save();
        $this->_checkPubsubEnabled($data);
        $blog->save();
        $feed->save();
        $flashMessenger->addMessage('Blog successfully added!');
        $flashMessenger->addMessage('success');
        $this->_redirect('/admin/blog/create');
    }
    
    protected function _checkPubsubEnabled(Zend_Feed_Reader_FeedAbstract $feed)
    {
        if (!$feed->getHubs()) {
            return;
        }
        $hubs = $feed->getHubs();;
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

}
