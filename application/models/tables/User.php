<?php

class TUser extends Zend_Db_Table_Abstract {

    protected $_name = 'user';
    protected $_primary = 'id_user';
    protected $_sequence = true;

    public function getByMail($mail)
    {
        $cache = Zend_Registry::get('cache');
        $cache_id = 'user_getbymail_' . md5($mail);
        $data = $this->fetchAll(
                $this->select()
                     ->where('mail = ?', $mail)
        )->current();
        return $data;
    }

    public function getById($id)
    {
        if ( ! is_numeric($id))
            return false;
        $data = $this->fetchAll(
                $this->select()
                     ->where('iduser = ?', $id))->current();
        return $data;
    }
  
    public function getByName($name)
    {

        $data = $this->fetchAll(
                $this->select()
                     ->where('nickname = ?', $name))->current();

        else
            $data = $cache->load($cache_id);
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