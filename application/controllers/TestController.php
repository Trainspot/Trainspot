<?php

class TestController extends Zend_Controller_Action
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
    public function indexAction()
    {
    }

    public function garesAction()
    {
        require_once APPLICATION_PATH . '/services/Sncf.php';
        $sncf = new Sncf();
        $res = $sncf->autocompleteGare("mass");
    }
}
?>