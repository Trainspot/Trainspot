<?php

class ApiController extends Zend_Controller_Action
{
    /**
     * Initialize action controller here
     */
    public function init() {
        Zend_Registry::set('api', false);
        if (empty($_GET['token']))
            return;

        $token = $_GET['token'];
        $auth = Zend_Auth::getInstance();
        $dbAdapter = Zend_Registry::get('dba');
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
        $authAdapter->setTableName('User');
        $authAdapter->setIdentityColumn('token');
        $authAdapter->setCredentialColumn('token');
        $authAdapter->setIdentity($token);
        $authAdapter->setCredential($token);

        $isAuthenticate = $auth->authenticate($authAdapter);
        if (!$isAuthenticate->isValid()) {
            die("{error: 'true', errorMsg: 'Token invalide'}");
        } else {
            Zend_Registry::set('api', true);
            $data = $authAdapter->getResultRowObject(null, 'token');
            $auth->getStorage()->write($data);
        }

        if ( ! Zend_Auth::getInstance()->hasIdentity())
        	die("{error: 'true', errorMsg: 'Vous devez être authentifié pour pouvoir accéder à l\'API'}");
    }    

    public function selfAction() {
    	echo Zend_Json::encode(Zend_Auth::getInstance()->getIdentity()); die();
    }

    public function trajetAction() {
    	require_once APPLICATION_PATH . '/models/tables/Travel.php';
    	$ttrajet = new Travel();
    	$trajets = $ttrajet->fetchAll($ttrajet->select()->where('id_user = ?', Zend_Auth::getInstance()->getIdentity()->id_user))->toArray();
    	die(Zend_Json::encode($trajets));
    }

    public function interetAction() {
		require_once APPLICATION_PATH . '/models/tables/Topic.php';
    	$ttopic = new Topic();
    	$topics = $ttopic->fetchAll($ttopic->select()->where('id_user = ?', Zend_Auth::getInstance()->getIdentity()->id_user))->toArray();
    	die(Zend_Json::encode($topics));
    }

    public function rencontreAction() {
		if ( ! Zend_Auth::getInstance()->hasIdentity())
            $this->_redirect('/');

        require_once '../application/services/Sncf.php';
        require_once '../application/models/tables/Travel.php';
        $travel = new Travel();
        $sncf = new Sncf();

        $my = $travel->fetchAll($travel->select()->where('id_user = ?', Zend_Auth::getInstance()->getIdentity()->id_user))->current();
        if ($my)
        {
            $gare_depart = $sncf->prepareAutocomplete($my['gare_depart']);
            $gare_arrivee = $sncf->prepareAutocomplete($my['gare_arrivee']);

            $t = $travel->fetchAll($travel->select()->where('id_user != ?', Zend_Auth::getInstance()->getIdentity()->id_user));
            $train = $sncf->searchTrains($gare_depart[0][1], $gare_arrivee[0][1], $my['time_depart']);
            if (empty($train['VehicleJourney']))
                $this->_redirect('/user/trajet');
            $train = $train['VehicleJourney'][0];
            $mytimes = $sncf->searchDepartArrival($train, $gare_depart[0][1], $gare_arrivee[0][1]);
            $results = array();
            foreach($t as $tr)
            {
                $gare_depart_other = $sncf->prepareAutocomplete($tr['gare_depart']);
                $gare_arrivee_other = $sncf->prepareAutocomplete($tr['gare_arrivee']);

                $trainother = $sncf->searchTrains($gare_depart_other[0][1], $gare_arrivee_other[0][1], $tr['time_depart']);
                if (empty($trainother['VehicleJourney']))
                    continue;
                $trainother = $trainother['VehicleJourney'][0];
                $timesother = $sncf->searchDepartArrival($trainother, $gare_depart_other[0][1], $gare_arrivee_other[0][1]);

                if ($mytimes['depart'] > $timesother['depart'])
                {
                    $garerencontre = $gare_depart[0][1];
                    $trainrencontre = $trainother;
                    $timerencontre = $mytimes['depart'];
                    $garerencontrecomplet = $gare_depart[0];
                }
                else
                {
                    $garerencontre = $gare_depart_other[0][1];
                    $trainrencontre = $train;
                    $timerencontre = $timesother['depart'];
                    $garerencontrecomplet = $gare_depart_other[0];
                }

                if ($sncf->searchTravel($trainrencontre, $garerencontre, $timerencontre))
                {
                    require_once '../application/models/tables/Topic.php';
                    require_once '../application/models/tables/User.php';
                    $user = new User();
                    $interest = new Topic();
                    $int = $interest->fetchAll($interest->select()->where('id_user = ?', $tr['id_user']));
                    $results[] = array(
                        'user' => $user->getById($tr['id_user']),
                        'gare_depart' => $gare_depart,
                        'gare_arrivee' => $gare_arrivee,
                        'times'     => $mytimes,
                        'interest' => $int,
                        'gare_rencontre' => $garerencontrecomplet,
                        'time_rencontre' => $timerencontre,
                        'train_rencontre' => $trainrencontre
                    );
                }
            }
            $this->view->results = $results;
            die(Zend_Json::encode($results));
        }
        else
        {
        	die("{error:true,errorMsg:'Vous devez avoir au moins un trajet'");
        }

    }
}