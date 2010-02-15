<?php

class BootstrapCli extends Zend_Application_Bootstrap_Bootstrap
{

    protected $_getopt = null;

    protected $_getOptRules = array(
        'environment|e-w' => 'Application environment switch (optional)',
        'module|m-w' => 'Module name (optional)',
        'controller|c=w' => 'Controller name (required)',
        'action|a=w' => 'Action name (required)'
    );

    protected function _initView()
    {
        // displaces View Resource class to prevent execution
    }

    protected function _initCliFrontController()
    {
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
        $getopt = new Zend_Console_Getopt($this->getOptionRules(),
            $this->_isolateMvcArgs());
        $request = new ZFExt_Controller_Request_Cli($getopt);
        $front->setResponse(new Zend_Controller_Response_Cli)
            ->setRequest($request)
            ->setRouter(new ZFExt_Controller_Router_Cli)
            ->setParam('noViewRenderer', true);
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
        if (function_exists('apc_add')) {
            $cacheDriver = new Doctrine_Cache_Apc;
            $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $cacheDriver);
        } 
        $manager->openConnection($doctrineConfig['connection_string']);
        Doctrine_Core::loadModels($doctrineConfig['models_path']);
        return $manager;
    }
    
    protected function _initErrorLog()
    {
        if (!file_exists(APPLICATION_PATH . '/../data/log/feedsync.log')) {
            return;
        }
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../data/log/feedsync.log');
        $log = new Zend_Log($writer);
        return $log;
    }
    
    protected function _initHtmlPurifier()
    {
        if (!defined('HTMLPURIFIER_PREFIX')) {
            define('HTMLPURIFIER_PREFIX', APPLICATION_PATH . '/../library');
        }
    }

    // CLI specific methods for option management

    public function setGetOpt(Zend_Console_Getopt $getopt)
    {
        $this->_getopt = $getopt;
    }

    public function getGetOpt()
    {
        if (is_null($this->_getopt)) {
            $this->_getopt = new Zend_Console_Getopt($this->getOptionRules());
        }
        return $this->_getopt;
    }

    public function addOptionRules(array $rules)
    {
        $this->_getOptRules = $this->_getOptRules + $rules;
    }

    public function getOptionRules()
    {
        return $this->_getOptRules;
    }

    // get MVC related args only (allows later uses of Getopt class
    // to be configured for cli arguments)
    protected function _isolateMvcArgs()
    {
        $options = array($_SERVER['argv'][0]);
        foreach ($_SERVER['argv'] as $key => $value) {
            if (in_array($value, array(
            '--action', '-a', '--controller', '-c', '--module', '-m', '--environment', '-e'
            ))) {
                $options[] = $value;
                $options[] = $_SERVER['argv'][$key+1];
            }
        }
        return $options;
    }

}
