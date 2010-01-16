<?php

class Admin_SubscribeController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->view->messages = $this->_helper
            ->getHelper('FlashMessenger')
            ->getMessages();
    }

    public function processAction()
    {
        if (Zend_Uri::check($_POST['topic_uri'])) {
            $result = $this->_subscribe($_POST['topic_uri']);
            if (!$result) {
                $message = 'Subscribing to ' . $_POST['topic_uri']
                 . ' failed. Either the feed was not Pubsubhubbub enabled'
                 . ' or the subscription attempt failed';
                $this->_helper->getHelper('FlashMessenger')
                    ->addMessage($message);
            } else {
                $this->_helper->getHelper('FlashMessenger')
                ->addMessage('Subscription Completed');
            }
        } else {
            $this->_helper->getHelper('FlashMessenger')
                ->addMessage('Topic URI is invalid');
        }
        $this->_helper->getHelper('Redirector')
            ->gotoUrl('/admin/subscribe');
    }

    protected function _subscribe($topic)
    {
        try {
            $feed = Zend_Feed_Reader::import($topic);
        } catch (Zend_Exception $e) {
            return false;
        }
        /**
         * Must use the URI of the feed contained in the feed itself in
         * case the original is no longer valid (e.g. feed moved and we just
         * followed a redirect to the new URI)
         */
        $feedTopicUri = $feed->getFeedLink();
        if (empty($feedTopicUri)) {
            return false;
        }
        /**
         * The feed may advertise one or more Hub Endpoints we can use.
         * We may subscribe to the Topic using one or more of the Hub
         * Endpoints advertised (good idea in case a Hub goes down).
         */
        $feedHubs = $feed->getHubs();
        if (is_null($feedHubs) || empty($feedHubs)) {
            return false;
        }
        /**
         * Carry out subscription operation...
         */
        $storage = new Zend_Feed_Pubsubhubbub_Storage_Filesystem;
        $storage->setDirectory(APPLICATION_ROOT . '/store/subscriptions');
        $options = array(
            'topicUrl' => $feedTopicUri,
            'hubUrls' => $feedHubs,
            'storage' => $storage,
            'callbackUrl' => 'http://hub.survivethedeepend.com/callback',
            'usePathParameter' => true,
            'authentications' => array(
                'http://superfeedr.com/hubbub' => array('padraicb','password')
            )
        );
        $subscriber = new Zend_Feed_Pubsubhubbub_Subscriber($options);
        $subscriber->subscribeAll();
        /**
         * Do some checking for errors...
         */
        if (!$subscriber->isSuccess()) {
            return false;
        }
        return true;
    }

}
