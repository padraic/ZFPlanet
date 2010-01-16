<?php

class Admin_Form_AddBlog extends ZFExt_Form
{

    public function init()
    {
        $this->setAction('/admin/blog/process');
        $this->setElementFilters(array('StringTrim', 'StripTags'));

        // Display Group #1 : Data

        $this->addElement('text', 'contactName', array(
            'decorators' => $this->_standardElementDecorator,
            'label' => 'Contact Name:',
            'attribs' => array(
                'maxlength' => 100,
                'size' => 60
            ),
            'validators' => array(
                array('StringLength', false, array(3,100))
            ),
            'required' => true
        ));

        $this->addElement('text', 'contactEmail', array(
            'decorators' => $this->_standardElementDecorator,
            'label' => 'Contact Email:',
            'attribs' => array(
                'maxlength' => 100,
                'size' => 60
            ),
            'validators' => array(
                array('StringLength', false, array(6,100)),
                array('EmailAddress')
            ),
            'required' => false
        ));
        
        $this->addElement('text', 'uri', array(
            'decorators' => $this->_standardElementDecorator,
            'label' => 'Blog URI:',
            'attribs' => array(
                'maxlength' => 255,
                'size' => 60
            ),
            'validators' => array(
                array('StringLength', false, array(12,255)),
                new ZFExt_Validate_Uri
            ),
            'required' => true
        ));

        $this->addElement('text', 'feedUri', array(
            'decorators' => $this->_standardElementDecorator,
            'label' => 'Feed URI:',
            'attribs' => array(
                'maxlength' => 255,
                'size' => 60
            ),
            'validators' => array(
                array('StringLength', false, array(12,255)),
                new ZFExt_Validate_Uri
            ),
            'required' => true
        ));

        $this->addDisplayGroup(
            array('contactName','contactEmail','uri','feedUri'),
            'addcomment',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => $this->_standardGroupDecorator,
                'legend' => 'Add New Blog'
            )
        );

        // Display Group #2 : Submit

        $this->addElement('submit', 'submit', array(
            'decorators' => $this->_buttonElementDecorator,
            'label' => 'Save'
        ));

        $this->addDisplayGroup(
            array('submit'), 'commentsubmit',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => $this->_buttonGroupDecorator,
                'class' => 'submit'
            )
        );
    }
}
