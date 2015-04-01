<?php

namespace Lce\web\http {
    use Lce\web\Application;
    use Lce\web\mvc\RouterManager;

    class Request
    {
        private static $_INSTANCE = null;

        private $_controllerName = null;
        private $_actionName = null;

        private $_routerParams = array();

        private function __construct()
        {
        }

        public function setControllerName($controllerName)
        {
            if ($this->_controllerName === null)
                $this->_controllerName = $controllerName;

            return $this;
        }

        public function setActionName($actionName)
        {
            if ($this->_actionName === null)
                $this->_actionName = $actionName;

            return $this;
        }

        public function getControllerName()
        {
            return $this->_controllerName;
        }

        public function getActionName()
        {
            return $this->_actionName;
        }

        public function getControllerActionName($separator = '_')
        {
            return $this->_controllerName . $separator . $this->_actionName;
        }

        public static function getInstance()
        {
            if (self::$_INSTANCE === null) {
                self::$_INSTANCE = new self;
            }

            return self::$_INSTANCE;
        }

        public function getServerParam($key, $defaultValue = null)
        {
            if (isset($_SERVER[$key]))
                return $_SERVER[$key];

            return $defaultValue;
        }

        public function setRouterParam($params)
        {
            if (is_array($params)) {
                foreach ($params as $key => $val) {
                    if (is_string($val))
                        $this->_routerParams[$key] = $val;
                }
            }
        }

        public function getRouterParam($key, $defaultValue = null)
        {
            if (isset($this->_routerParams[$key]))
                return $this->_routerParams[$key];

            return $defaultValue;
        }

        public function getGetParam($key, $defaultValue = null)
        {
            if (isset($_GET[$key]))
                return $_GET[$key];

            return $defaultValue;
        }

        public function getPostParam($key, $defaultValue = null)
        {
            if (isset($_POST[$key]))
                return $_POST[$key];

            return $defaultValue;
        }

        public function getParam($key, $defaultValue = null)
        {
            if (isset($_GET[$key]))
                return $_GET[$key];

            if (isset($_POST[$key]))
                return $_POST[$key];

            if (isset($this->_routerParams[$key]))
                return $this->_routerParams[$key];

            return $defaultValue;
        }

        public function getMethod()
        {
            return $_SERVER[ServerParamKey::REQUEST_METHOD];
        }

        public function isHttps()
        {
            return !empty($_SERVER[ServerParamKey::HTTPS]);
        }

        public function retrieveRequestUri()
        {
            if (isset($_SERVER[ServerParamKey::PATH_INFO])) {
                return trim($_SERVER[ServerParamKey::PATH_INFO], '/');
            }

            $requestUri = $_SERVER[ServerParamKey::REQUEST_URI];

            if (!empty($_SERVER[ServerParamKey::QUERY_STRING])) {
                $pattern = '/\?' . preg_quote($_SERVER[ServerParamKey::QUERY_STRING], '/') . '$/';
                $requestUri = preg_replace($pattern, '', $requestUri);
            }

            return str_replace(dirname($_SERVER[ServerParamKey::PHP_SELF]) . '/', '', $requestUri);
        }

        public function getHomeUrl($https = false, $withBaseDir = true)
        {
            $host = $this->getServerParam(ServerParamKey::HTTP_HOST);

            if ($host === null) {
                $host = $this->getServerParam(ServerParamKey::SERVER_NAME);
                $port = $this->getServerParam(ServerParamKey::SERVER_PORT);

                $host = $port === null ? $host : $host . ':' . $port;
            }

            return ($https ? 'https' : 'http') . '://' . $host .
            ($withBaseDir ? dirname($this->getServerParam(ServerParamKey::PHP_SELF)) : '');
        }

        public function getCurrentUrl($https = false)
        {
            return $this->getHomeUrl($https) . '/' . $this->retrieveRequestUri() .
            ($_SERVER[ServerParamKey::QUERY_STRING] ? '?' . $_SERVER[ServerParamKey::QUERY_STRING] : '');
        }

        public function getAssetsItemUrl($relativePath, $https = false)
        {
            $homeUrl = Request::getInstance()->getHomeUrl($https);
            return $homeUrl . '/' . Application::DIR_NAME_ASSETS . '/' . $relativePath;
        }

        public function getUrl($routeStr, $params = null, $https = false)
        {
            $params = is_array($params) ? $params : (is_object($params) ? get_object_vars($params) : array());

            if (preg_match('/^(http|https):\/\//', $routeStr, $matches) !== 1) {
                $homeUrl = $this->getHomeUrl($https);
                $routeUseWrite = RouterManager::getInstance()->rewrite;

                if ($routeUseWrite) {
                    $routeStr = $homeUrl . '/' . $routeStr;
                } else {
                    $params[RouterManager::ROUTER_PARAM_NAME] = $routeStr;
                    $routeStr = $homeUrl;
                }
            }

            return count($params) ? $routeStr . '?' . http_build_query($params) : $routeStr;
        }
    }
}