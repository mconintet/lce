<?php

namespace Lce\web {
    use Lce\web\http\Response;
    use Lce\web\log\Log;
    use Lce\web\mvc\Controller;
    use Lce\web\mvc\RouterManager;
    use Lce\web\util\BaseUtil;

    define('DS', DIRECTORY_SEPARATOR);
    define('PS', PATH_SEPARATOR);

    class Application
    {
        private static $_INSTANCE = null;

        const LCE_WEB_NAMESPACE = 'Lce\web';

        const CONFIG_KEY_APP = 'application';
        const CONFIG_KEY_APP_DIR = 'directory';
        const CONFIG_KEY_APP_NAMESPACE = 'namespace';
        const CONFIG_KEY_APP_TIMEZONE = 'timezone';
        const CONFIG_KEY_APP_ERROR_TEMPLATE = 'errorTemplate';

        const CONFIG_KEY_DEBUG_MODE = 'debugMode';
        const CONFIG_KEY_COMBINE_PRE_LOAD_CLASSES = 'combinePreLoadClasses';

        const CONFIG_KEY_EXTRA_INCLUDE_DIRS = 'extraIncludeDirs';
        const CONFIG_KEY_EXTRA_INCLUDE_DIR_NAMESPACE_ROOT = 'namespaceRoot';
        const CONFIG_KEY_EXTRA_INCLUDE_DIR_ABS_PATH = 'absolutePath';

        const CONFIG_KEY_PHP_INI_SET = 'php_ini_set';

        const DIR_NAME_CODE = 'code';
        const DIR_NAME_VIEWS = 'views';
        const DIR_NAME_CONFIGS = 'configs';
        const DIR_NAME_CONTROLLERS = 'controllers';
        const DIR_NAME_VAR = 'var';
        const DIR_NAME_DATA = 'data';

        const DIR_NAME_TEMPLATES = 'templates';
        const DIR_NAME_ASSETS = 'assets';

        const FILE_NAME_CONFIG_ORGANIZED = '__ORGANIZED__.php';
        const PRE_LOAD_CLASSES_FILE = '__PRE_LOAD_CLASSES__.php';

        private $_app_namespace = null;
        private $_app_dir = null;
        private $_app_error_template = null;

        private $_app_startup_dir;
        private $_lce_dir;
        private $_lce_web_dir;

        private $_extra_include_dirs = null;
        private $_has_extra_include_dirs = false;

        private $_config;

        private $_debugMode = false;

        private $_combinePreLoadClasses = false;

        private static $_isRunning = false;

        private function __construct($startupConfig)
        {
            if (is_array($startupConfig)) {
                if (isset($startupConfig[self::CONFIG_KEY_DEBUG_MODE]))
                    $this->_debugMode = $startupConfig[self::CONFIG_KEY_DEBUG_MODE];

                if (isset($startupConfig[self::CONFIG_KEY_COMBINE_PRE_LOAD_CLASSES])) {
                    $this->_combinePreLoadClasses = $startupConfig[self::CONFIG_KEY_COMBINE_PRE_LOAD_CLASSES];
                }
            }

            $this->_preLoadClasses($this->_combinePreLoadClasses);

            $this->_app_startup_dir = getcwd();
            $this->_lce_web_dir = __DIR__;
            $this->_lce_dir = realpath($this->_lce_web_dir . '/../..');

            $this->_config = $this->_retrieveConfig();
            $this->_app_dir = $this->_config[self::CONFIG_KEY_APP][self::CONFIG_KEY_APP_DIR];
            $this->_app_namespace = $this->_config[self::CONFIG_KEY_APP][self::CONFIG_KEY_APP_NAMESPACE];

            date_default_timezone_set($this->_config[self::CONFIG_KEY_APP][self::CONFIG_KEY_APP_TIMEZONE]);

            if (isset($this->_config[self::CONFIG_KEY_APP_ERROR_TEMPLATE]) &&
                is_readable($errorTemplate = $this->_config[self::CONFIG_KEY_APP_ERROR_TEMPLATE])
            ) {
                $this->_app_error_template = $errorTemplate;
            }

            if (isset($this->_config[self::CONFIG_KEY_PHP_INI_SET])) {
                $phpIniSet = $this->_config[self::CONFIG_KEY_PHP_INI_SET];

                foreach ($phpIniSet as $set) {
                    ini_set($set[0], $set[1]);
                }
            }

            if (isset($this->_config[self::CONFIG_KEY_EXTRA_INCLUDE_DIRS])) {
                $this->_extra_include_dirs = $this->_config[self::CONFIG_KEY_EXTRA_INCLUDE_DIRS];
                $this->_has_extra_include_dirs = true;
            }
        }

        public function debugMode($trueOrFalse)
        {
            $this->_debugMode = $trueOrFalse;
            return $this;
        }

        public function getLceDir()
        {
            return $this->_lce_dir;
        }

        public function getLceWebDir()
        {
            return $this->_lce_web_dir;
        }

        public function getVarDir()
        {
            return $this->_app_startup_dir . DS . self::DIR_NAME_VAR;
        }

        public function getCodeDir()
        {
            return $this->_app_startup_dir . DS . self::DIR_NAME_CODE;
        }

        public function getControllersDir()
        {
            return $this->getCodeDir() . DS . self::DIR_NAME_CONTROLLERS;
        }

        public function getAssetsDir()
        {
            return $this->_app_startup_dir . DS . self::DIR_NAME_ASSETS;
        }

        public function getTemplateDir()
        {
            return $this->getCodeDir() . DS . self::DIR_NAME_TEMPLATES;
        }

        public function getConfigsDir()
        {
            return $this->getCodeDir() . DS . self::DIR_NAME_CONFIGS;
        }

        private function _saveOrganizedConfig($organized)
        {
            $organizedFile = $this->getConfigsDir() . DS . self::FILE_NAME_CONFIG_ORGANIZED;

            try {
                file_put_contents($organizedFile, "<?php\n return " . var_export($organized, true) . ';');
            } catch (\Exception $e) {
                throw new \Exception("Can not save organized config with path '{$organizedFile}'");
            }
        }

        private function _generateNormalRouters()
        {
            $controllerClassDir = $this->getCodeDir() . DS . self::DIR_NAME_CONTROLLERS;
            $fileSystemIterator = new \FilesystemIterator($controllerClassDir);
            $normalRouters = array();
            $actionSuffix = Controller::ACTION_SUFFIX;

            if ($fileSystemIterator->isReadable()) {
                $classNamePrefix = $this->_app_namespace . '\\' . self::DIR_NAME_CODE . '\\' . self::DIR_NAME_CONTROLLERS . '\\';

                /**
                 * @var $fileInfo \SplFileInfo
                 */
                foreach ($fileSystemIterator as $fileInfo) {
                    $filename = $fileInfo->getFilename();

                    if (strpos($filename, '.') !== 0) {
                        include_once($fileInfo->getRealPath());

                        $className = $classNamePrefix . $fileInfo->getBasename('.php');
                        $reflectionClass = new \ReflectionClass($className);
                        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

                        foreach ($methods as $method) {
                            $methodName = $method->getShortName();

                            if (BaseUtil::stringEndWith($methodName, $actionSuffix)) {
                                $router = array(
                                    'from' => $reflectionClass->getShortName() . '/' . str_replace($actionSuffix, '', $methodName),
                                    'to' => $reflectionClass->getName()
                                );

                                $normalRouters[] = $router;
                            }
                        }
                    }
                }
            } else {
                throw new \Exception($controllerClassDir . ' is not a dir or not readable.');
            }

            return $normalRouters;
        }

        private function _retrieveConfig()
        {
            $configsDir = $this->getConfigsDir();
            $organized = $configsDir . DS . self::FILE_NAME_CONFIG_ORGANIZED;
            $config = array();

            if (!is_readable($configsDir) || !is_writable($configsDir))
                throw new \Exception("{$configsDir} muse be readable and writable.");

            if ($this->_debugMode || !file_exists($organized)) {
                $fileSystemIterator = new \FilesystemIterator($configsDir);
                /**
                 * @var $fileInfo \SplFileInfo
                 */
                foreach ($fileSystemIterator as $pathName => $fileInfo) {
                    if (($baseName = $fileInfo->getBasename('.php')) !== self::FILE_NAME_CONFIG_ORGANIZED &&
                        strpos($baseName, '.') !== 0
                    )
                        $config = BaseUtil::arrayMerge($config, include($pathName), true);
                }

                if (isset($config[self::CONFIG_KEY_APP]) &&
                    is_array($appSection = $config[self::CONFIG_KEY_APP])
                ) {
                    if (isset($appSection[self::CONFIG_KEY_APP_DIR]) &&
                        isset($appSection[self::CONFIG_KEY_APP_NAMESPACE]) &&
                        isset($appSection[self::CONFIG_KEY_APP_TIMEZONE])
                    ) {
                        if (!is_dir($appSection[self::CONFIG_KEY_APP_DIR])) {
                            throw new \Exception(sprintf('%s is not a directory or not exists',
                                $appSection[self::CONFIG_KEY_APP_NAMESPACE]));
                        } else {
                            $this->_app_dir = $appSection[self::CONFIG_KEY_APP_DIR];
                            $this->_app_namespace = $appSection[self::CONFIG_KEY_APP_NAMESPACE];
                        }
                    } else {
                        throw new \Exception(sprintf("You have to set '%s' '%s' '%s'",
                            self::CONFIG_KEY_APP_DIR, self::CONFIG_KEY_APP_NAMESPACE, self::CONFIG_KEY_APP_TIMEZONE));
                    }
                } else {
                    throw new \Exception(sprintf("You have to set '%s' section", self::CONFIG_KEY_APP));
                }

                if (isset($config[self::CONFIG_KEY_PHP_INI_SET])
                ) {
                    if (is_array(($phpIniSet = $config[self::CONFIG_KEY_PHP_INI_SET]))) {
                        foreach ($phpIniSet as $index => $set) {
                            if (!is_array($set) || !isset($set[0]) || !isset($set[1])) {
                                unset($config[self::CONFIG_KEY_PHP_INI_SET][$index]);
                            }
                        }
                    } else {
                        unset($config[self::CONFIG_KEY_PHP_INI_SET]);
                    }
                }

                if (isset($config[self::CONFIG_KEY_EXTRA_INCLUDE_DIRS])) {
                    if (is_array($extraIncludeDirs = $config[self::CONFIG_KEY_EXTRA_INCLUDE_DIRS])) {
                        foreach ($extraIncludeDirs as $index => $dir) {
                            if (isset($dir[self::CONFIG_KEY_EXTRA_INCLUDE_DIR_ABS_PATH]) &&
                                isset($dir[self::CONFIG_KEY_EXTRA_INCLUDE_DIR_NAMESPACE_ROOT])
                            ) {
                                $absPath = $dir[self::CONFIG_KEY_EXTRA_INCLUDE_DIR_ABS_PATH];
                                $absPath = rtrim(str_replace(array('/', '\\'), DS, $absPath), DS);

                                $this->_extra_include_dirs[] = array(
                                    self::CONFIG_KEY_EXTRA_INCLUDE_DIR_ABS_PATH => $absPath,
                                    self::CONFIG_KEY_EXTRA_INCLUDE_DIR_NAMESPACE_ROOT =>
                                        rtrim($dir[self::CONFIG_KEY_EXTRA_INCLUDE_DIR_NAMESPACE_ROOT], '\\')
                                );
                            } else {
                                unset($config[self::CONFIG_KEY_EXTRA_INCLUDE_DIRS][$index]);
                            }
                        }
                    } else {
                        unset($config[self::CONFIG_KEY_EXTRA_INCLUDE_DIRS]);
                    }
                }

                $oldRouterConfig = BaseUtil::arrayGetValueByPath($config, RouterManager::getNormalRoutesConfigPath());
                $routerConfig = BaseUtil::arrayMerge($this->_generateNormalRouters(), $oldRouterConfig, true);

                BaseUtil::arraySetValueByPath(
                    $config,
                    RouterManager::getNormalRoutesConfigPath(),
                    $routerConfig);

                if (!$this->_debugMode) {
                    $this->_saveOrganizedConfig($config);
                }
            } else {
                $organized = include($organized);
                if (is_array($organized))
                    $config = $organized;
            }

            return $config;
        }

        /**
         * @return Application
         */
        public static function getInstance()
        {
            return self::$_INSTANCE;
        }

        public function _classLoader($name)
        {
            $nameFixed = str_replace('\\', DS, $name);
            $classFilePath = null;

            if (strpos($name, self::LCE_WEB_NAMESPACE) === 0) {
                $classFilePath = $this->getLceDir() . DS . $nameFixed . '.php';
            } else if (strpos($name, $this->_app_namespace) === 0) {
                $classFilePath = $this->_app_dir . DS . $nameFixed . '.php';
            } else if ($this->_has_extra_include_dirs) {
                foreach ($this->_extra_include_dirs as $dir) {
                    if (strpos($name, $dir[self::CONFIG_KEY_EXTRA_INCLUDE_DIR_NAMESPACE_ROOT]) === 0) {
                        $classFilePath = $dir[self::CONFIG_KEY_EXTRA_INCLUDE_DIR_ABS_PATH] . DS . $nameFixed . '.php';
                        break;
                    }
                }
            }

            if ($classFilePath !== null)
                include($classFilePath);
        }

        public function _shutdownHandler()
        {
            if (($error = error_get_last()) !== null) {
                if (!$this->_debugMode) {
                    Response::getInstance()->eraseBody();
                    if ($this->_app_error_template !== null) {
                        include($this->_app_error_template);
                    } else {
                        echo 'Internal Error.';
                    }
                }

                Log::getInstance()->d($error);
                Response::getInstance()->addHeader(500)->send();
            } else {
                Response::getInstance()->send();
            }
        }

        /**
         * @param $path
         * @param null $defaultValue
         * @return null|mixed
         */
        public function getConfig($path, $defaultValue = null)
        {
            return BaseUtil::arrayGetValueByPath($this->_config, $path, $defaultValue);
        }


        public static function run($startupConfig = null)
        {
            if (self::$_isRunning === false) {
                self::$_INSTANCE = new self($startupConfig);

                register_shutdown_function(array(self::$_INSTANCE, '_shutdownHandler'));
                spl_autoload_register(array(self::$_INSTANCE, '_classLoader'));

                Response::getInstance()->start();
                RouterManager::getInstance()->dispatch();
            }
        }

        private function _getPreLoadClasses()
        {
            return array(
                'util/BaseUtil.php',
                'config/Configurable.php',
                'http/ServerParamKey.php',
                'http/Request.php',
                'http/Response.php',
                'event/EventContext.php',
                'event/EventManager.php',
                'mvc/Controller.php',
                'mvc/RouterManager.php',
                'log/Log.php',
                'log/adapter/BrowserAdapter.php',
                'log/adapter/FileAdapter.php',
                'log/adapter/SqliteAdapter.php'
            );
        }

        private function _preLoadClasses($combine)
        {
            if ($combine) {
                $dirName = $this->_app_startup_dir . DS .
                    self::DIR_NAME_CODE . DS .
                    self::DIR_NAME_DATA;

                $mergedFile = $dirName . DS . self::PRE_LOAD_CLASSES_FILE;

                if (is_readable($mergedFile)) {
                    include($mergedFile);
                } else if (is_writable($dirName) && is_readable($dirName) && is_readable(__DIR__)) {
                    $files = $this->_getPreLoadClasses();
                    $merged = array(
                        '<?php'
                    );

                    foreach ($files as $file) {
                        $fileContent = file_get_contents(__DIR__ . DS . $file);
                        $merged[] = str_replace('<?php', '', $fileContent);
                    }

                    file_put_contents($mergedFile, implode("\n", $merged));
                    include($mergedFile);
                } else {
                    throw new \Exception(sprintf('%s must be writable and readable', $dirName));
                }
            } else {
                $files = $this->_getPreLoadClasses();

                foreach ($files as $file) {
                    include(__DIR__ . DS . $file);
                }
            }
        }
    }
}