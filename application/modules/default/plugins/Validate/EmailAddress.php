<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikolay
 * Date: 04.10.12
 * Time: 15:36
 * To change this template use File | Settings | File Templates.
 */
class Default_Plugin_Validate_EmailAddress extends Zend_Validate_EmailAddress
{

    public function _error($messageKey, $value = null) {
        $this->_errors = array(self::INVALID);
    }

    public function getErrors() {
        return array(self::INVALID);
    }

    public function getMessages() {
        return array($this->_messageTemplates[self::INVALID]);
    }

}
