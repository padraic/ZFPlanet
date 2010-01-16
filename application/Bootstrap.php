<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initView()
    {
        $options = $this->getOptions();
        if (!isset($options['resources']['view'])) return;
        $config = $options['resources']['view'];
        if (isset($config)) {
            $view = new Zend_View($config);
        } else {
            $view = new Zend_View;
        }
        $view->setUseStreamWrapper(true);
        if (isset($config['doctype'])) {
            $view->doctype($config['doctype']);
        }
        if (isset($config['language'])) {
            $view->headMeta()->appendName('language', $config['language']);
        }
        if (isset($config['charset'])) {
            $view->headMeta()->setCharset($config['charset'], 'charset');
        }
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRenderer->setView($view);
        return $view;
    }
    
    protected function _initResponse()
    {
        $options = $this->getOptions();
        if (!isset($options['response']['defaultContentType'])) {
            return;
        }
        $response = new Zend_Controller_Response_Http;
        $response->setHeader('Content-Type',
            $options['response']['defaultContentType'], true);
        $this->bootstrap('FrontController');
        $this->getResource('FrontController')->setResponse($response);
    }
    
    protected function _initDoctrine()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('sfYaml')
            ->pushAutoloader(array('Doctrine', 'autoload'), 'sfYaml');
        $doctrineConfig = $this->getOption('doctrine');
        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
        $manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);
        $manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
        $manager->setAttribute(Doctrine_Core::ATTR_EXPORT, Doctrine_Core::EXPORT_ALL);
        $manager->setCharset('utf8');
        $manager->setCollate('utf8_unicode_ci'); 
        $manager->openConnection($doctrineConfig['connection_string']);
        Doctrine_Core::loadModels($doctrineConfig['models_path']);
        return $manager;
    }
    
    protected function _initHtmlPurifier()
    {
        if (!defined('HTMLPURIFIER_PREFIX')) {
            define('HTMLPURIFIER_PREFIX', APPLICATION_PATH . '/../library');
        }
    }

}
