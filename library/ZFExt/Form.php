<?php

class ZFExt_Form extends Zend_Form
{

    protected $_standardElementDecorator = array(
        'ViewHelper',
        array('LabelError', array('escape'=>false)),
        array('HtmlTag', array('tag'=>'li'))
    );

    protected $_buttonElementDecorator = array(
        'ViewHelper'
    );

    protected $_standardGroupDecorator = array(
        'FormElements',
        array('HtmlTag', array('tag'=>'ol')),
        'Fieldset'
    );

    protected $_buttonGroupDecorator = array(
        'FormElements',
        'Fieldset'
    );

    protected $_noElementDecorator = array(
        'ViewHelper'
    );

    public function __construct($options = null)
    {
        // Path setting for custom classes MUST ALWAYS be first!
        $this->addElementPrefixPath('ZFExt_Form_Decorator', 'ZFExt/Form/Decorator/', 'decorator');
        $this->addElementPrefixPath('ZFExt_Filter', 'ZFExt/Filter/', 'filter');
        $this->addElementPrefixPath('ZFExt_Validate', 'ZFExt/Validate/', 'validate');
        $this->addPrefixPath('ZFExt_Form_Element', 'ZFExt/Form/Element/', 'element');

        //$this->_setupTranslation();

        parent::__construct($options);

        $this->setAttrib('accept-charset', 'UTF-8');
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));
    }

    protected function _setupTranslation()
    {
        if (self::getDefaultTranslator()) {
            return;
        }
        $path = Bootstrap::$root . '/translate/forms.php';
        $translate = new Zend_Translate('array', $path, 'en');
        self::setDefaultTranslator($translate);
    }

}
