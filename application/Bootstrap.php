<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function run() {
        Zend_Registry::set('config', new Zend_Config($this->getOptions()));
        Zend_Registry::set('user_target', array());
        parent::run();
    }

    protected function _initSalt() {
        Zend_Registry::set('static_salt', 'p42sds54f5');
    }

    protected function _initRouter() {
        $front = $this->bootstrap('FrontController')->getResource('FrontController');
        $router = $front->getRouter();
    }

    protected function _initReferer()
    {
        $session = new Zend_Session_Namespace('Default');
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
}

