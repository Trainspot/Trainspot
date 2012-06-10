<?php

class IndexController extends Zend_Controller_Action
{
    /**
     * Initialize action controller here
     */
    public function init()
    {
        
    }

    /**
     * action body
     */
    public function indexAction() {
    }

    public function registerAction() {
        $this->view->mail = $this->_getParam('mail');
        $this->view->nom = $this->_getParam('nom');
        $this->view->prenom = $this->_getParam('prenom');
        $this->view->ddn = $this->_getParam('ddn');
        $this->view->phone = $this->_getParam('phone');
    }
}
?>