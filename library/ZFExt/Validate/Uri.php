<?php

require_once 'Zend/Validate/Abstract.php';
require_once 'Zend/Uri.php';


class ZFExt_Validate_Uri extends Zend_Validate_Abstract
{
    const MSG_URI = 'msgUri';

    protected $_messageTemplates = array(
        self::MSG_URI => "Invalid URI",
    );

    public function isValid($value)
    {
        $this->_setValue($value);  
        if (Zend_Uri::check($value))  {
            return true;
        } else {
            $this->_error(self::MSG_URI);
            return false;
        }

    }
}
