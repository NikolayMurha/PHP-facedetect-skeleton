<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Murga Nikolay work@murga.kiev.ua
 * Date: 27.04.12
 * Time: 13:48
 */


class Smarty_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Smarty_View
     */
    protected $_view;

    /**
     * Strategy pattern: initialize resource
     *
     * @return mixed
     */
    public function init()
    {
        $view = $this->getView();
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setView($view);

        $options = $this->getOptions();

        $options2 = $this->getBootstrap()->getApplication()->getOptions();

        try {
            if (isset($options2['resources']['layout'])) {
                $this->getBootstrap()->bootstrap('layout');
                $layout = $this->getBootstrap()->getResource('layout');
                $layout->setView($view);
            }
        } catch(Exception $e) {
            print $e->getMessage();
        }

        if (isset($options['viewRenderer'])) {
            foreach($options['viewRenderer'] as $key=>$value) {
                $setter = 'set'.ucfirst($key);
                if (method_exists($viewRenderer, $setter)) {
                    $viewRenderer->$setter($value);
                }
            }
        }
        return $view;
    }

    /**
     * Retrieve view object
     *
     * @return Zend_View
     */
    public function getView()
    {
        //$this->getBootstrap()->bootstrap('modules');

        if (null === $this->_view) {
            $options = $this->getOptions();
            $this->_view = new Smarty_View($options);

            if (isset($options['assign']) && is_array($options['assign'])) {
                $this->_view->assign($options['assign']);
            }
        }
        return $this->_view;
    }
}
