<?php

class Default_Model_BuyHistory extends Zend_Db_Table_Row_Abstract
{
    protected $_name = 'bb.buy_history';

    protected $_primary = array( 'user_id', 'track_id' );

    public function save() {
        if (!$this->id) {
            $this->created_at = new Zend_Db_Expr('NOW()');
            $this->updated_at = new Zend_Db_Expr('NOW()');
            parent::save();
            $this->log('Create order');
        } else {
            $this->updated_at = new Zend_Db_Expr('NOW()');
            parent::save();
        }
    }

    public function getTrackInfo() {

    }

}

