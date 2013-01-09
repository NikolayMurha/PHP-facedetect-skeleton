<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function _initSessionstart() {
        $this->bootstrap('session');
        $this->bootstrap('modules');
        Zend_Session::start();
    }

    public function _initConfig() {
        $this->bootstrap('Smarty_Resource_View');
        $view  = $this->getResource('Smarty_Resource_View');

        $options = $this->getOptions();
        Zend_Registry::set('options', $options);
        return $options;
    }
}