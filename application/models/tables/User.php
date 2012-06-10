<?php

class User extends Zend_Db_Table_Abstract {

    protected $_name = 'User';
    protected $_primary = 'id_user';
    protected $_sequence = true;

    public function getByMail($mail)
    {
        $data = $this->fetchAll(
                $this->select()
                     ->where('email = ?', $mail)
        )->current();
        return $data;
    }

    public function getById($id)
    {
        if ( ! is_numeric($id))
            return false;
        $data = $this->fetchAll(
                $this->select()
                     ->where('id_user = ?', $id))->current()->toArray();
        return $data;
    }
  
    public function insert(array $data)
    {
        return parent::insert($data);
    }

    public function remove($id)
    {
        if ( ! is_numeric($id))
            return false;

        return $this->delete('iduser = ' . $id);
    }

}