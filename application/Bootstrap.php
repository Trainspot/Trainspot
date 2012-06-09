<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function run() {
        Zend_Registry::set('config', new Zend_Config($this->getOptions()));
        Zend_Registry::set('user_target', array());
        parent::run();
    }

    protected function _initCache() {
        $frontendOptions = array(
           'lifetime' => 60000,
           'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => '../tmp/'
        );

        $cache = Zend_Cache::factory('Core',
                                     'File',
                                     $frontendOptions,
                                     $backendOptions);

        Zend_Registry::set('cache', $cache);

        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
    }

    protected function _initRouter() {
        $front = $this->bootstrap('FrontController')->getResource('FrontController');
        $router = $front->getRouter();
    }

    protected function _initReferer()
    {
        $session = new Zend_Session_Namespace('Default');
        $session->tmp = md5(rand());
        if (isset($session->referer))
        {
            $referer = $session->referer;
            unset($session->referer);
        }
        else
            $referer = '/';
        Zend_Registry::set('referer', $referer);
    }
    
    protected function _initDb() {
        $config = new Zend_Config($this->getOptions());
        try {
            $db = Zend_Db::factory($config->resources->db);
            $db->getConnection();
        } catch (Exception $e) {
            exit($e->getMessage());
        }
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Registry::set('dba', $db);
        return $db;
    }

    protected function _initWebshell() {
        require_once APPLICATION_PATH . '/../library/Webshell.php';
        $webshell = Webshell::getInstance();
        $webshell->init("47844ded8999967dae45662d1e8c449a");
        $webshell->setUserId(session_id());
    }
}

