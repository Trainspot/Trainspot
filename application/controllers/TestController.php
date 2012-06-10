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
        $res = $sncf->prepareAutocomplete("mass");
        var_dump($res);die();
    }
/*
    public function corrAction()
    {
        require_once APPLICATION_PATH . '/services/Sncf.php';
        $sncf = new Sncf();
        $gare1 = $sncf->prepareAutocomplete("igny");
        $gare2 = $sncf->prepareAutocomplete("pont-cardinet");
        $gare1 = $gare1[0];
        $gare2 = $gare2[0];
        $res = $sncf->hasCorrespondance($gare1[1], $gare2[1]);
        var_dump($res);die();
    }*/

    public function searchtrainAction()
    {
        require_once APPLICATION_PATH . '/services/Sncf.php';
        $sncf = new Sncf();
        $gare = $sncf->prepareAutocomplete('igny');
        $gare2 = $sncf->prepareAutocomplete('VERSAILLES-CHANTIERS');
        $res = $sncf->searchTrains($gare[0][1], $gare2[0][1], time());
        var_dump($res);
        die();
    }

    public function searchtravelAction()
    {
        $t = 1339319062;
        require_once APPLICATION_PATH . '/services/Sncf.php';
        $sncf = new Sncf();
        $gare = $sncf->prepareAutocomplete('igny');
        $gare2 = $sncf->prepareAutocomplete('juvisy');
        $gare_otherguy = $sncf->prepareAutocomplete('massy-palaiseau');

        $res = $sncf->searchTrains($gare[0][1], $gare2[0][1], $t);
        $res = $res['VehicleJourney'][0]; // Get first journey found

        $times = $sncf->searchDepartArrival($res, $gare[0][1], $gare2[0][1]);
        var_dump("depart: " . date('H|i', $times['depart']));
        var_dump("arrival: " . date('H|i', $times['arrival']));
        $res = $sncf->searchTravel($res, $gare_otherguy[0][1], $times['depart'] + 60 * 5);
        var_dump($res);
        die();
    }
}
?>