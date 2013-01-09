<?php
/**
 * Created by IntelliJ IDEA.
 * Date: 21.12.12
 * Time: 15:38
 * To change this template use File | Settings | File Templates.
 */
class Default_Controller_Action_Helper_Auth extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Auth_Adapter_Interface
     */
    protected $adapter;

    /**
     * @var Zend_Auth
     */
    protected $service;

    public function __construct()
    {
        $request = $this->getRequest();
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');

        $urlHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('url');

        //$bootstrap = $this->getActionController()->getInvokeArg('bootstrap');
        $config = $bootstrap->getOptions();
        $config = $config['authorization'];
        $config['callbackUri'] = $request->getScheme() . '://' . $request->getHttpHost() . $urlHelper->url(array('controller' => 'auth', 'module' => 'default', 'action' => 'callback'));

        if (!empty($config['developer_mode'])) {
            $authAdapter = new TuneHog_Accounts_Auth_MockAdapter($config);
        } else {
            $authAdapter = new TuneHog_Accounts_Auth_Adapter($config);
        }
        $this->setService(Zend_Auth::getInstance());
        $this->setAdapter($authAdapter);
    }

    public function checkAndGotoAuth()
    {
        if (!$this->getService()->hasIdentity()) {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('url');

            $url = $helper->simple('login', 'auth', 'default');
            $continue = $helper->url();
            $redirector->gotoUrlAndExit($url . '?continue=' . $continue);
        }
    }

    /**
     * @param Zend_Auth_Adapter_Interface $adapter
     * @return Zend_Auth_Result
     */
    public function authenticate(\Zend_Auth_Adapter_Interface $adapter = null) {
        if (!$adapter) {
            $adapter = $this->getAdapter();
        }
        return $this->getService()->authenticate($adapter);
    }
    /**
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->getService()->getIdentity();
    }

    /**
     * @return bool
     */
    public function hasIdentity()
    {
        return $this->getService()->hasIdentity();
    }

    /**
     * @return bool
     */
    public function clearIdentity()
    {
        return $this->getService()->clearIdentity();
    }

    /**
     * @param mixed $identity
     * @return string
     */
    public function getLogoutUrl($identity = null)
    {
        if (!$identity) {
            $identity = $this->getService()->getIdentity();
        }

        return $this->getAdapter()->getLogoutUrl($identity);
    }

    /**
     *  @return string
     */
    public function getLoginUrl()
    {
        return $this->getAdapter()->start();
    }

    /**
     * @return \Zend_Auth
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param \Zend_Auth $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return \Zend_Auth_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param \Zend_Auth_Adapter_Interface $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }


}
