<?php

class UserController extends Zend_Controller_Action
{
    /**
     * Initialize action controller here
     */
    public function init()
    {
        
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
    
    public function setupAction() {
        if ($this->getRequest()->isPost())
        {
            require_once '../application/services/Sncf.php';
            require_once '../models/tables/'
            $garedepart = $this->_getParam('gare-depart');
            $garearrive = $this->_getParam('gare-arrivee');
            $sncf = new Sncf();
            $l = $sncf->prepareAutocomplete($garedepart);
            $ligne = $l[4];
            $timeh = $this->_getParam('depart-hour');
            $timem = $this->_getParam('depart-min');
            if ($garedepart == '' || $garearrive == '' || empty($ligne) || empty($timeh) || empty($timem))
            {
                //erreur
            }
            else
            {
                
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
                    $this->_redirect('/user/setup');
                }
                else
                {
                    $this->view->error = true;
                    $this->view->errorMsg = 'Error interne';
                }
            }
        }            
    }
}
?>