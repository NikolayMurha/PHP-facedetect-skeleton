<?php
class Default_View_Helper_Auth extends Zend_View_Helper_Abstract
{

    protected $auth;

    public function Auth() {
        return $this;
    }
    public function __construct()
    {
        $this->authHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Auth');
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->authHelper, $method)) {
            return call_user_func_array(array($this->authHelper, $method), $arguments);
        }
    }
}

