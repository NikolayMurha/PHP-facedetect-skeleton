<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikolay
 * Date: 03.10.12
 * Time: 12:46
 * To change this template use File | Settings | File Templates.
 */
class Default_Model_UserTable extends Zend_Db_Table_Abstract
{
    protected $_name = 'bb.users';

    protected $_rowClass = 'Default_Model_User';
    protected $_primary = array( 'id' );

    /**
     * Return or create new user if not exist
     * @param $data
     *
     */
    public function getUserByData($data) {

        $select = $this->select()->where('email=?', $data['email']);
        $user = $this->fetchRow($select);
        if (!$user) {
            $user = $this->createRow(array(
                'email' => $data['email'],
            ));
            $user->save();
        }
        $user->assign($data);
        return $user;
    }
}
