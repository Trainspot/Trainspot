<?php

class UserController extends Zend_Controller_Action
{
    /**
     * Initialize action controller here
     */
    public function init() {
        
    }    

    public function fbmeAction() {
        require_once 'Webshell.php';
        $wsh = Webshell::getInstance();
        $mail = $this->_getParam('mail');
        require_once '../application/models/tables/User.php';
        $user = new User();
        $verif = $user->getByMail($mail);
        if ($verif)
        {
            $this->login($mail);
            die('ok');
        }
        else
            die('nok');
    }
    
    public function trajetAction() {

        if ( ! Zend_Auth::getInstance()->hasIdentity())
            $this->_redirect('/');

        require_once '../application/models/tables/Travel.php';
        $travel = new Travel();
        $t = $travel->fetchAll($travel->select()->where('id_user = ?', Zend_Auth::getInstance()->getIdentity()->id_user))->current();
        if ($t)
        {
            $this->view->gare_depart = $t['gare_depart'];
            $this->view->gare_arrivee = $t['gare_arrivee'];
            $this->view->ligne = $t['ligne'];
            $this->view->flexible = $t['flexible'];
            $this->view->time_depart = $t['time_depart'];
        }
        else
        {
            $this->view->gare_depart = '';
            $this->view->gare_arrivee = '';
            $this->view->ligne = '';
            $this->view->flexible = '';
            $this->view->time_depart = '';
        }

        if ($this->getRequest()->isPost())
        {
            require_once '../application/services/Sncf.php';
//            require_once '../models/tables/';
            $garedepart = $this->_getParam('gare-depart');
            $garearrive = $this->_getParam('gare-arrivee');
            $sncf = new Sncf();
            $l = array_shift($sncf->prepareAutocomplete($garedepart));
            $ligne = $l[4];
            $timeh = $this->_getParam('depart-hour');
            $timem = $this->_getParam('depart-min');
            $timestamp = mktime($timeh, $timem, 0);
            if ($garedepart == '' || $garearrive == '' || empty($ligne) || empty($timeh) || empty($timem))
            {
                $this->_redirect('/error/error');
            }
            else
            {
                require_once '../application/models/tables/Travel.php';
                $travel = new Travel();
                $t = $travel->fetchAll($travel->select()->where('id_user = ?', Zend_Auth::getInstance()->getIdentity()->id_user))->current();
                if ($t)
                {
                    $where = $travel->getAdapter()->quoteInto('id_user = ?', Zend_Auth::getInstance()->getIdentity()->id_user);
                    $travel->update(array(
                        'ligne' => $ligne,
                        'gare_depart' => $garedepart,
                        'gare_arrivee' => $garearrive,
                        'flexible' => (($this->_getParam('regulier', 0) != 0) ? 1 : 0),
                        'id_user' => Zend_Auth::getInstance()->getIdentity()->id_user,
                        'time_depart' => $timestamp
                    ), $where);
                }
                else
                    $travel->insert(array(
                        'ligne' => $ligne,
                        'gare_depart' => $garedepart,
                        'gare_arrivee' => $garearrive,
                        'flexible' => (($this->_getParam('regulier', 0) != 0) ? 1 : 0),
                        'id_user' => Zend_Auth::getInstance()->getIdentity()->id_user,
                        'time_depart' => $timestamp
                    ));
                $this->_redirect('/user/interest');
            }
        }
    }


    public function autocompletegareAction() {
        require_once '../application/services/Sncf.php';
        $sncf = new Sncf();
        $str = $this->_getParam('term');
        $data = $sncf->autocompleteGare($str);
        die(Zend_Json::encode($data));
    }

    public function login($mail) {
        $auth = Zend_Auth::getInstance();
        $dbAdapter = Zend_Registry::get('dba');
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
        $authAdapter->setTableName('User');
        $authAdapter->setIdentityColumn('email');
        $authAdapter->setCredentialColumn('email');
        $authAdapter->setIdentity($mail);
        $authAdapter->setCredential($mail);

        $isAuthenticate = $auth->authenticate($authAdapter);
        if (!$isAuthenticate->isValid()) {
            $this->view->error = true;
            $this->view->errorMsg = $isAuthenticate->getMessages();
            return false;
        } else {
            $data = $authAdapter->getResultRowObject(null, 'pass');
            $auth->getStorage()->write($data);
        }
    }
    /**
     * action body
     */
    public function registerAction()
    {
        $mail = $this->_getParam('mail');
        $nom = $this->_getParam('nom');
        $prenom = $this->_getParam('prenom');
        $ddn = $this->_getParam('ddn');
        $this->view->email = $mail;
        $this->view->nom = $nom;
        $this->view->prenom = $prenom;
        $this->view->ddn = $ddn;
        if ($mail == '' || $nom == '' || $prenom == '' || $ddn == '')
        {
            $this->view->error = true;
            $this->view->errorMsg = "Veuillez remplir tous les champs";
        }
        else
        {
            require_once '../application/models/tables/User.php';
            $user = new User();
            $verif = $user->getByMail($mail);
            if ($verif)
            {
                $this->view->error = true;
                $this->view->errorMsg = "L'addresse email est déjà prise !";
            }
            else
            {
                $ok = $user->insert(array(
                    'email' => $mail,
                    'firstname' => $prenom,
                    'lastname' => $nom,
                    'date_naissance' => $ddn
                ));
                if ($ok)
                {
                    $this->view->error = false;
                    $this->login($mail);
                    $this->_redirect('/user/');
                }
                else
                {
                    $this->view->error = true;
                    $this->view->errorMsg = 'Error interne';
                }
            }
        }            
    }


    /**
     * action body
     */
    public function interestAction() {
        require_once '../application/models/tables/Topic.php';
        $topic = new Topic();

        $this->view->needs = $topic->fetchAll($topic->select()->where('id_user = ? AND need_proposal = 1', Zend_Auth::getInstance()->getIdentity()->id_user));
        $this->view->proposals = $topic->fetchAll($topic->select()->where('id_user = ? AND need_proposal = 0', Zend_Auth::getInstance()->getIdentity()->id_user));

        if ($this->getRequest()->isPost())
        {
            $proposal = $this->_getParam('proposal');
            $proposaltitle = $this->_getParam('titleproposal');
            $need = $this->_getParam('need');
            $needtitle = $this->_getParam('titleneed');

            $topic->insert(array(
                'title' => $proposaltitle,
                'desc' => $proposal,
                'need_proposal' => 0,
                'id_user' => Zend_Auth::getInstance()->getIdentity()->id_user
            ));
            $topic->insert(array(
                'title' => $needtitle,
                'desc' => $need,
                'need_proposal' => 1,
                'id_user' => Zend_Auth::getInstance()->getIdentity()->id_user
            ));
            die('ok');
        }
    }

    /**
     * action body
     */
    public function indexAction() {

    }

    static public function getAge($user) {

        $birthday = explode("/", $user->date_naissance);

        $ageTime = mktime(0, 0, 0, $birthday[0], $birthday[1], $birthday[2]); // Get the person's birthday timestamp
        $t = time(); // Store current time for consistency
        $age = ($ageTime < 0) ? ( $t + ($ageTime * -1) ) : $t - $ageTime;
        $year = 60 * 60 * 24 * 365;
        $ageYears = $age / $year;

        return floor($ageYears);

    }

}
?>