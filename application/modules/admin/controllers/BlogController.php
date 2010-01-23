<?php

class Admin_BlogController extends Zend_Controller_Action
{

    public function createAction()
    {
        $form = new Admin_Form_AddBlog;
        $this->view->addBlogForm = $form;
    }
    
    public function editAction()
    {

    }
    
    public function processAction()
    {
        $form = new Admin_Form_AddBlog;
        $this->view->success = true;
        if (!$this->getRequest()->isPost()) {
            return $this->_forward('admin/index');
        }
        if (!$form->isValid($_POST)) {
            $this->view->success = false;
            $this->view->addBlogForm = $form;
        }
        $values = $form->getValues();
        try {
            $blog = new Zfplanet_Model_Blog;
            $blog->contactName = $values['contactName'];
            if (isset($values['contactEmail'])) {
                $blog->contactEmail = $values['contactEmail'];
            }
            $blog->uri = $values['uri'];
            $data = Zend_Feed_Reader::import($values['feedUri']);
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
            $feed->isActive = 1;
            $feed->save();
            $this->_checkPubsubEnabled($data);
            $blog->save();
            $feed->save();
            $this->_redirect('/admin');
        //} catch (Exception $e) {
            //$this->view->success = false;
            //$this->view->addBlogForm = $form;
        //}
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
            $this->_helper->getHelper('Url')->simple(null, 'callback', 'zfplanet')
        );
        //try {
            $sub->subscribeAll();
        //} catch (Exception $e) {/*do something about failures later*/}
    }

}
