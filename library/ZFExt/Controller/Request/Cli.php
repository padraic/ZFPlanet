<?php

require_once 'Zend/Controller/Request/Abstract.php';

class ZFExt_Controller_Request_Cli extends Zend_Controller_Request_Abstract
{

    protected $_getopt = null;

    public function __construct(Zend_Console_Getopt $getopt)
    {
        $this->_getopt = $getopt;
        if ($getopt->{$this->getModuleKey()}) {
            $this->setModuleName($getopt->{$this->getModuleKey()});
        }
        if ($getopt->{$this->getControllerKey()}) {
            $this->setControllerName($getopt->{$this->getControllerKey()});
        }
        if ($getopt->{$this->getActionKey()}) {
            $this->setActionName($getopt->{$this->getActionKey()});
        }
    }

}
