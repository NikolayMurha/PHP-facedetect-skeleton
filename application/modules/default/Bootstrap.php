<?php

    class Default_Bootstrap extends Zend_Application_Module_Bootstrap {
        public function _initHelpers() {
            include_once "FirePHPCore/fb.php";
            //Plugin
            $front = Zend_Controller_Front::getInstance();
            //$front->registerPlugin(new Default_Plugin_CheckUsers());

            /*$this->getApplication()->bootstrap('Smarty_Resource_View');
            $view = $this->getApplication()->getResource('Smarty_Resource_View');
            $view->addHelperPath(dirname(__FILE__).'/views/helpers', 'Default_View_Helper');*/

            // \Zend_Controller_Action_HelperBroker::addPath(dirname(__FILE__).'/'. DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'helpers', "Default_Controller_Action_Helper");
            //Action Helper
            //Zend_Controller_Action_HelperBroker::addPath(dirname(__FILE__).'/plugins/Controller/Helper', 'User_Plugin_Controller_Helper');
        }

        public function _initConfig() {
            return new Zend_Config_Ini(dirname(__FILE__).'/configs/module.ini');
        }
    }
