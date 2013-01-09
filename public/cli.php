<?php



// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Define application environment
defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', realpath(dirname(__FILE__)));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run

$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap();

class My_Router extends Zend_Controller_Router_Abstract implements Zend_Controller_Router_Interface
{
    public function assemble($userParams, $name = null, $reset = false, $encode = true) {

    }

    public function route(Zend_Controller_Request_Abstract $dispatcher) {
        $argv = array_slice($_SERVER['argv'], 1);

        @list($action, $controller, $module) = explode(':', $argv[0]);
        if (!$module) {
            $module = 'default';
        }

        if (!$controller) {
            $controller = 'index';
        }

        if (!$action) {
            $action = 'index';
        }

        //$dispatcher = new Zend_Controller_Request_Http();
        $dispatcher->setModuleName($module);
        $dispatcher->setControllerName($controller);
        $dispatcher->setActionName($action);
        return $dispatcher;
    }
}

$front = Zend_Controller_Front::getInstance();
$front->setRouter(new My_Router);
$front->dispatch();