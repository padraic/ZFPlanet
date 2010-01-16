<?php

class ZFExt_View_Proxy
{

    protected $_data = null;
    
    protected $_view = null;
    
    public function __construct($data, ZFExt_View $view)
    {
        $this->_data = $data;
        $this->_view = $view;
    }
    
    public function getRaw()
    {
        return $this->_data;
    }
    
    public function __set($key, $value)
    {
        $this->_data->{$key} = $value;
    }
    
    public function __get($key)
    {
        $returnProxy = new Zend_View_Proxy($this->_data->{$key}, $this->_view);
        return $returnProxy;
    }
    
    public function __isset($key)
    {
        return isset($this->_data->{$key});
    }
    
    public function __unset($key)
    {
        unset($this->_data->{$key});
    }
    
    public function __call($method, array $args)
    {
        $return = call_user_func_array(array($this->_data, $method), $args);
        $returnProxy = new ZFExt_View_Proxy($return, $this->_view);
        return $returnProxy;
    }
    
    public function __toString()
    {
        return $this->_view->escape((string) $this->_data);
    }

}
