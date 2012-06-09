<?php
defined('DEV_KEY')          || define('DEV_KEY', 'AI39si7Vm8TorTrAYuKytKenVidS4FUbYIt6eoFpIqDQTasnuZW-3MPMcIMoFZj_DZAyDfQPszEAVlbw5bi2LViczLPUjdeUbA');
defined('APPLICATION_ID')   || define('APPLICATION_ID', 'HumanKindWall');
defined('CLIENT_ID')        || define('CLIENT_ID', 'HumanKindWall');

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

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();