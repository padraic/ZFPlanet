<?php

class Zfplanet_CronController extends Zend_Controller_Action
{

    public function init()
    {
        if (!$this->getRequest() instanceof ZFExt_Controller_Request_Cli) {
            throw new Exception('Access denied from HTTP');
        }
    }

    /**
     * Polls all registered feeds. The actual polling is performed by the
     * retrieved Models, so all we do here is get those Models and setup
     * Zend_Feed_Reader caching/conditional GET support if configured.
     * Polling is NOT performed for feeds which have a confirmed Pubsubhubbub
     * subscription active.
     *
     * @return void
     */
    public function pollAction()
    {
        try {
            $feeds = Doctrine_Query::create()
                ->from('Zfplanet_Model_Feed f')
                ->where(
                    'f.uri NOT IN (SELECT s.topic_url FROM Zfplanet_Model_Subscription s'
                    . ' WHERE s.subscription_state = ?)',
                    Zend_Feed_Pubsubhubbub::SUBSCRIPTION_VERIFIED)
                ->execute();
            if (!$feeds) {
                return;
            }
            $chelper = $this->_helper->getHelper('Cache');
            if ($chelper->hasCache('feed')) {
                Zend_Feed_Reader::setCache($chelper->getCache('feed'));
                Zend_Feed_Reader::useHttpConditionalGet();
            }
            $notifier = $this->_getTwitterNotifier();
            foreach($feeds as $feed) {
                if ($notifier->isEnabled()) $feed->setTwitterNotifier($notifier);
                $feed->synchronise();
            }
            $this->_helper->getHelper('Cache')->removePagesTagged(array('allentries'));
            $this->_doPubsubhubbubNotification();
        } catch (Exception $e) {
            var_dump($e);
        }
    }
    
    protected function _doPubsubhubbubNotification()
    {
        $this->_helper->notifyHub(array('http://pubsubhubbub.appspot.com'));
    }
    
    protected function _getTwitterNotifier()
    {
        $notifier = new Zfplanet_Model_Service_TwitterNotifier(
            $this->getInvokeArg('bootstrap')->getOptions(),
            $this->_helper->getHelper('Cache')->getCache('twitter')
        );
        return $notifier;
    }

}
