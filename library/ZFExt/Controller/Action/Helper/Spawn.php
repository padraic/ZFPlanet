<?php

class ZFExt_Controller_Action_Helper_Spawn
    extends Zend_Controller_Action_Helper_Abstract
{

    protected $_scriptPath = null;

    protected $_defaultScriptPath = null;

    public function setScriptPath($script = null)
    {
        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32') {
            $script = str_replace('/', '\\', $script);
        }
        $this->_scriptPath = $script;
        return $this;
    }

    public function setDefaultScriptPath($script)
    {
        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32') {
            $script = str_replace('/', '\\', $script);
        }
        $this->_defaultScriptPath = $script;
        return $this;
    }

    public function direct(array $parameters = null, $action = null,
    $controller = null, $module = null)
    {
        if (is_null($parameters)) {
            $parameters = array();
        } else {
            foreach ($parameters as $key => $value) {
                $parameters[$key] = escapeshellarg($value);
            }
        }
        if ($module) {
            $parameters['-m'] = escapeshellarg($module);
        }
        if ($controller) {
            $parameters['-c'] = escapeshellarg($controller);
        }
        if ($action) {
            $parameters['-a'] = escapeshellarg($action);
        }
        $this->_spawnProcess($parameters);
        $this->_scriptPath = null; // reset
    }

    protected function _spawnProcess(array $args)
    {
        if (is_null($this->_scriptPath)) {
            $script = $this->_defaultScriptPath;
        } else {
            $script = $this->_scriptPath;
        }
        $command = 'php ' . $script;
        foreach ($args as $key => $value) {
            $command .= ' ' . $key . ' ' . $value;
        }
        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32') {
            $pcommand = 'start /b ' . $command;
        } else {
            $pcommand = $command . ' > /dev/null &';
        }
        pclose(popen($pcommand, 'r'));
    }

}
