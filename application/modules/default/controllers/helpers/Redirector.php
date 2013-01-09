<?php

/**
 * @author Nikolay Murga <nikolay.m@randrmusic.com>
 */
class Default_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Redirector
{
    const SESSION_NAMESPACE = 'redirector';


    public function init() {
        $this->proceedInit();
    }
    /**
     * @param null $url
     * @internal param \Zend_Http_Client $client
     * @internal param string $method
     * @return \Zend_Http_Response
     */
    public function proceed($url = null)
    {
        $session = new \Zend_Session_Namespace(static::SESSION_NAMESPACE);
        if ($url) {
            $this->gotoUrl($url);
            return;
        }

        if ($session->continue) {
            $url = $session->continue;
            $session->continue = null;
            $this->gotoUrl($url);
        } elseif ($this->getRequest()->getActionName() != 'index') {
            $this->gotoSimple('index');
        }
    }

    public function proceedInit()
    {
        $session = new \Zend_Session_Namespace(static::SESSION_NAMESPACE);
        if ($this->getRequest()->continue) {
            $session->continue = $this->getRequest()->continue;
        }
    }
}