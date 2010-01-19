<?php

class ZFExt_Controller_Plugin_LayoutSwitcher extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $front = Zend_Controller_Front::getInstance();
        if ($front->getDefaultModule() == $request->getModuleName()) {
            return;
        }
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayoutPath(
            $front->getModuleDirectory(
                $request->getModuleName()    
            ) . '/views/layouts'
        );
        $layout->setLayout('default');
    }

}
