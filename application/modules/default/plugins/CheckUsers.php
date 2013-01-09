<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nik
 * Date: 29.04.12
 * Time: 0:33
 */
class Default_Plugin_CheckUsers extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $request = $this->getRequest();
        $token = $request->getCookie('accounts_token');
        $identity = Zend_Auth::getInstance()->hasIdentity();

        /** @var Zend_Controller_Action_Helper_Redirector $redirector */
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        /** @var Default_Controller_Action_Helper_Auth $auth */
        $auth = Zend_Controller_Action_HelperBroker::getStaticHelper('auth');



        // if callback url
        if ($request->getActionName() == 'callback'
            && $request->getModuleName() == 'default' && $request->getControllerName() == 'auth'
        ) {
            return;
        }

        if ($token && $identity) {
            return;
        }

        if (!$token) {
            $auth->clearIdentity();
            $redirector->gotoUrlAndExit('http://tunehog.com');
        }

        if (!$identity) {
            $redirector->gotoUrlAndExit($auth->getLoginUrl());
        }
    }
}
