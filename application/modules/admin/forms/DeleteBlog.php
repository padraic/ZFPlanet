<?php

class Admin_Form_DeleteBlog extends ZFExt_Form
{

    public function init()
    {
        $this->setAction('/admin/blog/process2');
        $this->setElementFilters(array('StringTrim', 'StripTags'));

        // Display Group #1 : Data

        $this->addElement('multiCheckbox', 'feeds', array(
            'decorators' => $this->_standardElementDecorator,
            'required' => true
        ));

        $this->addDisplayGroup(
            array('feeds'),
            'deleteblog',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => $this->_standardGroupDecorator,
                'legend' => 'Delete Blogs'
            )
        );

        // Display Group #2 : Submit

        $this->addElement('submit', 'submit', array(
            'decorators' => $this->_buttonElementDecorator,
            'label' => 'Delete'
        ));

        $this->addDisplayGroup(
            array('submit'), 'deleteblogsubmit',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => $this->_buttonGroupDecorator,
                'class' => 'submit'
            )
        );
    }
}
