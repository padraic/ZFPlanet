<?php

class Zfplanet_CronController extends Zend_Controller_Action
{

    public function init()
    {
        if (!$this->getRequest() instanceof ZFExt_Controller_Request_Cli) {
            throw new Exception('Access denied from HTTP');
        }
    }

    public function pollAction()
    {
        $feeds = Doctrine_Query::create()
            ->from('Zfplanet_Model_Feed')
            ->execute();
        if (!$feeds) {
            return;
        }
        foreach($feeds as $feed) {
            $feed->synchronise();
        }
    }

}
