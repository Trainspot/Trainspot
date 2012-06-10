<?php

class DevelopersController extends Zend_Controller_Action
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
        if ( ! Zend_Auth::getInstance()->hasIdentity())
            $this->_redirect('/');
        $identity = Zend_Auth::getInstance()->getIdentity();
        $this->view->token = $identity->token;
    }

}
?>