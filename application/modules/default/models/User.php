<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikolay
 * Date: 03.10.12
 * Time: 12:46
 * To change this template use File | Settings | File Templates.
 */
class Default_Model_User extends Zend_Db_Table_Row
{
    protected $_name = 'bb.users';

    protected $_primary = array( 'id' );

    /**
     * @var TuneHog_Graph_Client
     */
    protected $_storage;

    protected $_dataExt = array();

    public function assign($data) {
        $this->_dataExt = array_merge($this->_dataExt, $data, $this->_data);
    }

    public function toArray() {
        return array_merge($this->_dataExt, $this->_data);
    }

    public function __get($varName) {
        if (isset($this->_dataExt[$varName])) {
            return $this->_dataExt[$varName];
        }
        return parent::__get($varName);
    }

    public function __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = '_dataExt';
        return $sleep;
    }

    public function __wakeup() {
        $this->setTable(new Default_Model_UserTable);
    }
    
    public function __isset($columnName)
    {
        $columnName = $this->_transformColumn($columnName);
        if (array_key_exists($columnName, $this->_dataExt)) {
            return true;
        }
        return array_key_exists($columnName, $this->_data);
    }

    /**
     * @return null|TuneHog_Graph_Model_Interface
     */
    public function getNativeModel() {
        if (!$this->oauth_uid) {
            return null;
        }

        return $this->getStorage()->find($this->oauth_uid);
    }

    /**
     * @return TuneHog_Graph_Client
     */
    public function getStorage() {
        if (!$this->_storage) {
            $this->_storage = new TuneHog_Graph_Client();
        }
        return $this->_storage;
    }

    /**
     * @param \TuneHog_Graph_Client $storage
     * @return \Default_Model_User
     */
    public function setStorage($storage)
    {
        $this->_storage = $storage;
        return $this;
    }
}
