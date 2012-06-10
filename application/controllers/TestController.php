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
        var_dump($res);die();
    }

    public function corrAction()
    {
        require_once APPLICATION_PATH . '/services/Sncf.php';
        $sncf = new Sncf();
        $gare1 = $sncf->autocompleteGare("igny");
        $gare2 = $sncf->autocompleteGare("pont-cardinet");
        $gare1 = $gare1[0];
        $gare2 = $gare2[0];
        $res = $sncf->hasCorrespondance($gare1[1], $gare2[1]);
        var_dump($res);die();
    }
    public function searchtrainAction()
    {
        require_once APPLICATION_PATH . '/services/Sncf.php';
        $sncf = new Sncf();
        $gare = $sncf->autocompleteGare('igny');
        $gare2 = $sncf->autocompleteGare('VERSAILLES-CHANTIERS');
        $res = $sncf->searchTrains($gare[0], $gare2[0], time() - 3600 * 12);
        //var_dump($res);
        die();
    }
}
?>