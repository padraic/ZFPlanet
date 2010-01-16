<?php

class ZFExt_View extends Zend_View
{

    protected $_proxyData = array();

    public function __set($key, $val)
    {
        if ('_' != substr($key, 0, 1)) {
            $this->_proxyData->{$key} = $val;
            return;
        }

        require_once 'Zend/View/Exception.php';
        $e = new Zend_View_Exception('Setting private or protected class members is not allowed');
        $e->setView($this);
        throw $e;
    }
    
    public function __get($key)
    {
        if ($this->_strictVars && !isset($this->_proxyData->{$key})) {
            trigger_error('Key "' . $key . '" does not exist', E_USER_NOTICE);
            return null;
        } else {
            $returnProxy = new ZFExt_View_Proxy($this->_proxyData->{$key}, $this);
            return $returnProxy;
        }
    }
    
    public function __isset($key)
    {
        if ('_' != substr($key, 0, 1)) {
            return isset($this->_proxyData->{$key});
        }

        return false;
    }
    
    public function __unset($key)
    {
        if ('_' != substr($key, 0, 1) && isset($this->_proxyData->{$key})) {
            unset($this->_proxyData->{$key});
        }
    }

}
