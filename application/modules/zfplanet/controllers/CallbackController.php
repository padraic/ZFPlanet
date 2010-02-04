<?php

class Zfplanet_CallbackController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $storage = Doctrine_Core::getTable('Zfplanet_Model_Subscription');
        $callback = new Zend_Feed_Pubsubhubbub_Subscriber_Callback;
        $callback->setStorage($storage);
        $callback->setSubscriptionKey($this->_getParam('subscriptionKey'));
        $callback->handle();
        if ($callback->hasFeedUpdate()) {
            $data = $callback->getFeedUpdate();
            $key = md5($data);
            file_put_contents(APPLICATION_PATH . '/../data/tmp/' . $key, $data);
            $this->_helper->getHelper('Spawn')
                ->setScriptPath(APPLICATION_PATH . '/../scripts/zf-cli');
            $this->_helper->spawn(
                array('--key'=>$key), 'process', 'callback'
            );
        }
        $callback->sendResponse();
    }

    public function processAction()
    {
        if (!$this->getRequest() instanceof ZFExt_Controller_Request_Cli) {
            throw new Exception('Access denied from HTTP');
        }
        $this->getInvokeArg('bootstrap')->addOptionRules(
            array('key|k=s' => 'File keyname for task data (required)')
        );
        $options = $this->getInvokeArg('bootstrap')->getGetOpt();
        $path = APPLICATION_PATH . '/../data/tmp/' . $options->key;
        $data = file_get_contents($path);
        $feed = Zend_Feed_Reader::importString($data);
        unlink($path);
        $feedModel = Doctrine_Core::getTable('Zfplanet_Model_Feed')->find($feed->getId());
        if ($feedModel) {
            $notifier = $this->_getTwitterNotification();
            if ($notifier->isEnabled()) $feedModel->setTwitterNotifier($notifier);
            $feedModel->synchronise($feed);
            $this->_helper->getHelper('Cache')->removePagesTagged(array('allentries'));
            $this->_helper->notifyHub(array('http://pubsubhubbub.appspot.com/'));
        } else {
            throw new Exception('Unable to parse feed containing: ' . $data);
        }
    }
    
    /**
     * Duplicated in TwitterController of Admin Module - refactor to action helper TODO
     */
    protected function _getTwitterNotifier()
    {
        $notifier = new Zfplanet_Model_Service_TwitterNotifier(
            $this->getInvokeArg('bootstrap')->getOptions(),
            $this->_helper->getHelper('Cache')->getCache('twitter');
        );
        return $notifier;
    }


}
