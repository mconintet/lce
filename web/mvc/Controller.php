<?php

namespace Lce\web\mvc {
    use Lce\web\http\Request;
    use Lce\web\http\Response;

    abstract class Controller
    {
        const ACTION_SUFFIX = 'Action';

        private $_regexpRouterParams;

        public function __construct($regexpRouterParams)
        {
            if (is_array($regexpRouterParams))
                $this->_regexpRouterParams = $regexpRouterParams;
        }

        protected function getParam($key, $defaultValue = null)
        {
            if (isset($this->_regexpRouterParams[$key]))
                return $this->_regexpRouterParams[$key];

            return Request::getInstance()->getParam($key, $defaultValue);
        }

        protected function getGetParam($key, $defaultValue = null)
        {
            return Request::getInstance()->getGetParam($key, $defaultValue);
        }

        protected function getPostParam($key, $defaultValue = null)
        {
            return Request::getInstance()->getPostParam($key, $defaultValue);
        }

        public function getRequest()
        {
            return Request::getInstance();
        }

        public function isPostMethod()
        {
            return Request::getInstance()->getMethod() === 'POST';
        }

        public function isGetMethod()
        {
            return Request::getInstance()->getMethod() === 'GET';
        }

        public function getUrl($routeStr, $params = null, $https = false)
        {
            return Request::getInstance()->getUrl($routeStr, $params, $https);
        }

        public function redirect($target, $params = null, $https = false)
        {
            $url = Request::getInstance()->getUrl($target, $params, $https);
            Response::getInstance()->setRedirect($url);
        }

        public function getHomeUrl($https = false, $withBaseDir = true)
        {
            return Request::getInstance()->getHomeUrl($https, $withBaseDir);
        }

        public function getCurrentUrl($https = false)
        {
            return Request::getInstance()->getCurrentUrl($https);
        }

        /**
         * @return boolean return false to skip running real action
         */
        abstract protected function _beforeDoActionMethod();

        /**
         * @param $actionName
         * @return bool
         */
        public function doActionMethod($actionName)
        {
            $actionName = $actionName . self::ACTION_SUFFIX;

            if ($this->_beforeDoActionMethod() !== false) {
                $this->$actionName();
            }
        }
    }
}