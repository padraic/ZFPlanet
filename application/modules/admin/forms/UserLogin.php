<?php

class Admin_Form_UserLogin extends ZFExt_Form
{

    public function init()
    {
        $this->setAction('/admin/user/process');
        $this->setElementFilters(array('StringTrim', 'StripTags'));

        $this->addElement('text', 'name', array(
            'decorators' => $this->_standardElementDecorator,
            'label' => 'User Name:',
            'attribs' => array(
                'maxlength' => 100,
                'size' => 60
            ),
            'validators' => array(
                array('StringLength', false, array(3,25))
            ),
            'required' => true
        ));

        $this->addElement('password', 'password', array(
            'decorators' => $this->_standardElementDecorator,
            'label' => 'Password:',
            'attribs' => array(
                'maxlength' => 100,
                'size' => 60
            ),
            'validators' => array(
                array('StringLength', false, array(6,100)),
            ),
            'required' => true
        ));

        $this->addDisplayGroup(
            array('name','password'),
            'userlogin',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => $this->_standardGroupDecorator,
                'legend' => 'User Login'
            )
        );

        $this->addElement('hash', 'token', array(
            'decorators' => $this->_buttonElementDecorator,
            'salt' => hash('sha1', uniqid(rand(), TRUE)),
            'timeout' => 300
        ));
        
        $this->addElement('submit', 'submit', array(
            'decorators' => $this->_buttonElementDecorator,
            'label' => 'Login'
        ));
        

        $this->addDisplayGroup(
            array('token','submit'), 'loginsubmit',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => $this->_buttonGroupDecorator,
                'class' => 'submit'
            )
        );
    }
}
