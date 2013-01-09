<?php

class Default_ErrorController extends Zend_Controller_Action
{

    public function init()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getHelper('layout')->disableLayout();
        }
        ;
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // ошибка 404 - не найден контроллер или действие
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
                $content = <<<EOH
<h1>Error!</h1>
<p>Page not found.</p>
EOH;

                break;
            default:
                // ошибка приложения
                $content = <<<EOH
<h1>Error!</h1>
<p>Page not found.</p>
EOH;

                break;
        }

        $this->getResponse()->clearBody();

        $exception = $errors->exception;
        $this->view->exception = $exception;
        $this->view->message = $content;
    }


}

