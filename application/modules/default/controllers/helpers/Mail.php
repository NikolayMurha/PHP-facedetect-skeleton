<?php
/**
 * Created by IntelliJ IDEA.
 * Date: 21.12.12
 * Time: 15:38
 * To change this template use File | Settings | File Templates.
 */ 
class Default_Controller_Action_Helper_Mail extends Zend_Controller_Action_Helper_Abstract {

    public function send($config) {

        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        $mail = new Zend_Mail();
        // Render email
        if (empty($config['scriptPath'])) {
            $module = $this->getRequest()->getModuleName();
            $moduleDir = $this->getFrontController()->getModuleDirectory($module);
            $config['scriptPath'] = $moduleDir . '/views/email';
        }

        /**
         * @var Zend_View_Interface $view
         */
        $view = $this->getActionController()->getHelper('layout')->getView();
        $view = clone $view;
        $view->setScriptPath($config['scriptPath']);
        $view->assign($config['view']);

        foreach($config['view'] as $var => $value) {
            if (!is_string($value)) {
                continue;
            }
            $config['subject'] = str_replace('%'.$var.'%', $value, $config['subject']);
        }
        $config['subject'] = str_replace('%from%', $config['from'], $config['subject']);

        //Set email config
        $mail->setFrom($config['from']);
        $mail->setSubject($config['subject']);
        $mail->setBodyHtml($view->render($config['template']));

        foreach((array)$config['to'] AS $to) {
            $mail->addTo($to);
        }

        return $mail->send();
    }
}
