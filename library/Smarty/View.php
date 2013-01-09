<?php
require_once dirname(__FILE__) . '/vendor/Smarty.class.php';
class Smarty_View implements Zend_View_Interface
{
    /**
     * Объект Smarty
     * @var Smarty
     */
    protected $_smarty;

    public $_helper;

    private $_loaders = array();

    /**
     * Plugin types
     * @var array
     */
    private $_loaderTypes = array('filter', 'helper');

    /**
     * Конструктор
     *
     * @param array $options
     * @return \Smarty_View
     */
    public function __construct($options = array())
    {
        $this->_smarty = new Smarty;

        //spike-nail
        spl_autoload_unregister('smartyAutoload');
        spl_autoload_register('smartyAutoload', false, true);

        if (isset($options['pluginsDir'])) {
            $this->_smarty->addPluginsDir($options['pluginsDir']);
            unset($options['pluginsDir']);
        }

        foreach ($options as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this->_smarty, $setter)) {
                $this->_smarty->$setter($value);
            } elseif (property_exists($this->_smarty, $key)) {
                $this->_smarty->$key = $value;
            }
        }

        $this->_smarty->assign('this', $this);
    }

    /**
     * Возвращение объекта шаблонизатора
     *
     * @return Smarty
     */
    public function getEngine()
    {
        return $this->_smarty;
    }

    /**
     * Установка пути к шаблонам
     *
     * @param string $path Директория, устанавливаемая как путь к шаблонам
     * @throws Exception
     * @return bool
     */
    public function setScriptPath($path)
    {
        if (is_readable($path)) {
            $this->_smarty->setTemplateDir($path);
            return true;
        }

        throw new Exception('Invalid path provided');
    }

    /**
     * Извлечение текущего пути к шаблонам
     *
     * @return string
     */
    public function getScriptPaths()
    {
        return $this->_smarty->getTemplateDir();
    }

    /**
     * Метод-"псевдоним" для setScriptPath
     *
     * @param string $path
     * @param string $prefix Не используется
     * @return bool
     */
    public function setBasePath($path, $prefix = 'Zend_View')
    {
        return $this->setScriptPath($path);
    }

    /**
     * Метод-"псевдоним" для setScriptPath
     *
     * @param string $path
     * @param string $prefix Не используется
     * @return bool
     */
    public function addBasePath($path, $prefix = 'Zend_View')
    {
        return $this->setScriptPath($path);
    }

    /**
     * Присвоение значения переменной шаблона
     *
     * @param string $key Имя переменной
     * @param mixed $val Значение переменной
     * @return void
     */
    public function __set($key, $val)
    {
        $this->_smarty->assign($key, $val);
    }

    /**
     * Получение значения переменной
     *
     * @param string $key Имя переменной
     * @return mixed Значение переменной
     */
    public function __get($key)
    {
        return $this->_smarty->getTemplateVars($key);
    }

    /**
     * Получение значения переменной
     *
     * @param string $key Имя переменной
     * @return mixed Значение переменной
     */
    public function getVars()
    {
        return $this->_smarty->getTemplateVars();
    }

    /**
     * Позволяет проверять переменные через empty() и isset()
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return (null !== $this->_smarty->getTemplateVars($key));
    }

    /**
     * Позволяет удалять свойства объекта через unset()
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->_smarty->clearAssign($key);
    }

    /**
     * Присвоение переменных шаблону
     *
     * Позволяет установить значение к определенному ключу или передать массив
     * пар ключ => значение
     *
     * @see __set()
     * @param string|array $spec Ключ или массив пар ключ => значение
     * @param mixed $value (необязательный) Если присваивается значение одной
     * переменной, то через него передается значение переменной
     * @return void
     */
    public function assign($spec, $value = null)
    {
        if (is_array($spec)) {
            $this->_smarty->assign($spec);
            return;
        }

        $this->_smarty->assign($spec, $value);
    }

    /**
     * Удаление всех переменных
     *
     * @return void
     */
    public function clearVars()
    {
        $this->_smarty->clearAllAssign();
    }

    /**
     * Обрабатывает шаблон и возвращает вывод
     *
     * @param string $name Шаблон для обработки
     * @return string Вывод
     */
    public function render($name)
    {
        return $this->_smarty->fetch($name);
    }


    /**
     * Set plugin loader for a particular plugin type
     *
     * @param  Zend_Loader_PluginLoader $loader
     * @param  string $type
     * @throws Zend_View_Exception
     * @return Zend_View_Abstract
     */
    public function setPluginLoader(Zend_Loader_PluginLoader $loader, $type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_loaderTypes)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"', $type));
            $e->setView($this);
            throw $e;
        }

        $this->_loaders[$type] = $loader;
        return $this;
    }

    /**
     * Retrieve plugin loader for a specific plugin type
     *
     * @param  string $type
     * @throws Zend_View_Exception
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_loaderTypes)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"; cannot retrieve', $type));
            $e->setView($this);
            throw $e;
        }

        if (!array_key_exists($type, $this->_loaders)) {
            $prefix = 'Zend_View_';
            $pathPrefix = 'Zend/View/';

            $pType = ucfirst($type);
            switch ($type) {
                case 'filter':
                case 'helper':
                default:
                    $prefix .= $pType;
                    $pathPrefix .= $pType;
                    $loader = new Zend_Loader_PluginLoader(array(
                                                                $prefix => $pathPrefix
                                                           ));
                    $this->_loaders[$type] = $loader;
                    break;
            }
        }
        return $this->_loaders[$type];
    }

    /**
     * Adds to the stack of helper paths in LIFO order.
     *
     * @param string|array The directory (-ies) to add.
     * @param string $classPrefix Class prefix to use with classes in this
     * directory; defaults to Zend_View_Helper
     * @return Zend_View_Abstract
     */
    public function addHelperPath($path, $classPrefix = 'Zend_View_Helper_')
    {
        return $this->_addPluginPath('helper', $classPrefix, (array)$path);
    }

    /**
     * Resets the stack of helper paths.
     *
     * To clear all paths, use Zend_View::setHelperPath(null).
     *
     * @param string|array $path The directory (-ies) to set as the path.
     * @param string $classPrefix The class prefix to apply to all elements in
     * $path; defaults to Zend_View_Helper
     * @return Zend_View_Abstract
     */
    public function setHelperPath($path, $classPrefix = 'Zend_View_Helper_')
    {
        unset($this->_loaders['helper']);
        return $this->addHelperPath($path, $classPrefix);
    }

    /**
     * Get full path to a helper class file specified by $name
     *
     * @param  string $name
     * @return string|bool False on failure, path on success
     */
    public function getHelperPath($name)
    {
        return $this->_getPluginPath('helper', $name);
    }

    /**
     * Returns an array of all currently set helper paths
     *
     * @return array
     */
    public function getHelperPaths()
    {
        return $this->getPluginLoader('helper')->getPaths();
    }

    /**
     * Registers a helper object, bypassing plugin loader
     *
     * @param  Zend_View_Helper_Abstract|object $helper
     * @param  string $name
     * @return Zend_View_Abstract
     * @throws Zend_View_Exception
     */
    public function registerHelper($helper, $name)
    {
        if (!is_object($helper)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('View helper must be an object');
            $e->setView($this);
            throw $e;
        }

        if (!$helper instanceof Zend_View_Interface) {
            if (!method_exists($helper, $name)) {
                require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(
                    'View helper must implement Zend_View_Interface or have a method matching the name provided'
                );
                $e->setView($this);
                throw $e;
            }
        }

        if (method_exists($helper, 'setView')) {
            $helper->setView($this);
        }

        $name = ucfirst($name);
        $this->_helper[$name] = $helper;
        return $this;
    }

    /**
     * Get a helper by name
     *
     * @param  string $name
     * @return object
     */
    public function getHelper($name)
    {
        return $this->_getPlugin('helper', $name);
    }

    /**
     * Get array of all active helpers
     *
     * Only returns those that have already been instantiated.
     *
     * @return array
     */
    public function getHelpers()
    {
        return $this->_helper;
    }

    /**
     * Get a path to a given plugin class of a given type
     *
     * @param  string $type
     * @param  string $name
     * @return string|bool
     */
    private function _getPluginPath($type, $name)
    {
        $loader = $this->getPluginLoader($type);
        if ($loader->isLoaded($name)) {
            return $loader->getClassPath($name);
        }

        try {
            $loader->load($name);
            return $loader->getClassPath($name);
        } catch (Zend_Loader_Exception $e) {
            return false;
        }
    }

    /**
     * Retrieve a plugin object
     *
     * @param  string $type
     * @param  string $name
     * @return object
     */
    private function _getPlugin($type, $name)
    {
        $name = ucfirst($name);
        switch ($type) {
            case 'helper':
                $storeVar = '_helper';
                $store = $this->_helper;
                break;
        }

        if (!isset($store[$name])) {
            $class = $this->getPluginLoader($type)->load($name);
            $store[$name] = new $class();
            if (method_exists($store[$name], 'setView')) {
                $store[$name]->setView($this);
            }
        }

        $this->$storeVar = $store;
        return $store[$name];
    }

    /**
     * Add a prefixPath for a plugin type
     *
     * @param  string $type
     * @param  string $classPrefix
     * @param  array $paths
     * @return Zend_View_Abstract
     */
    private function _addPluginPath($type, $classPrefix, array $paths)
    {
        $loader = $this->getPluginLoader($type);
        foreach ($paths as $path) {
            $loader->addPrefixPath($classPrefix, $path);
        }
        return $this;
    }

    /**
     * Accesses a helper object from within a script.
     *
     * If the helper class has a 'view' property, sets it with the current view
     * object.
     *
     * @param string $name The helper name.
     * @param array $args The parameters for the helper.
     * @return string The result of the helper output.
     */
    public function __call($name, $args)
    {
        // is the helper already loaded?
        $helper = $this->getHelper($name);

        // call the helper method
        return call_user_func_array(
            array($helper, $name),
            $args
        );
    }

    public function escape($string) {
        return htmlspecialchars($string);
    }

}