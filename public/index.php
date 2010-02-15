<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

/** Zend_Cache */
require_once 'Zend/Cache.php';

/** Zend_Config_Ini */
require_once 'Zend/Config/Ini.php';

// Check for cached configuration, or create one before passage to Zend_App
// This should speed up bootstrapping by some small margin
$frontendOptions = array(
    'name' => 'File',
    'params' => array(
        'lifetime' => null,
        'automatic_cleaning_factor' => 0,
        'automatic_serialization' => true,
        'master_files' => array(
            APPLICATION_PATH . '/configs/application.ini',
            APPLICATION_PATH . '/configs/site.ini',
            APPLICATION_PATH . '/configs/http.ini'
        )
    )
);
$backendOptions = array(
    'name' => 'File',
    'params' => array(
        'cache_dir' => APPLICATION_PATH . '/../data/cache/config',
    )
);
$configCache = Zend_Cache::factory(
    $frontendOptions['name'],
    $backendOptions['name'],
    $frontendOptions['params'],
    $backendOptions['params']
);
$finalConfig = null;
if (!($finalConfig = $configCache->load('configuration'))) {
    $configFiles = array(
        APPLICATION_PATH . '/configs/application.ini',
        APPLICATION_PATH . '/configs/http.ini',
        APPLICATION_PATH . '/configs/site.ini'
    );
    $masterConfig = null;
    foreach($configFiles as $file) {
        $config = new Zend_Config_Ini($file, APPLICATION_ENV, array('allowModifications'=>true));
        if (is_null($masterConfig)) {
            $masterConfig = $config;
        } else {
            $masterConfig->merge($config);
        }
    }
    $finalConfig = $masterConfig->toArray();
    $configCache->save($finalConfig, 'configuration');
}

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    $finalConfig
);
$application->bootstrap()
            ->run();
